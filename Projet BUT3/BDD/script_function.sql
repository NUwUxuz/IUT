/*
* =================================================================================
* |-                             Fonctions / Triggers
* =================================================================================
*/

SET SCHEMA 'sae';


CREATE OR REPLACE FUNCTION update_generic_listens()
RETURNS TRIGGER AS $$
DECLARE
    fk_column_name TEXT;
    target_table   TEXT;
    target_column  TEXT;
    record_id      INT;
    diff           INT;
BEGIN
    fk_column_name := TG_ARGV[0]; 
    target_table   := TG_ARGV[1];
    target_column  := TG_ARGV[2];

    IF (TG_OP = 'INSERT') THEN
        diff := NEW.nb_listening;
    ELSIF (TG_OP = 'UPDATE') THEN
        diff := NEW.nb_listening - OLD.nb_listening;
    ELSE
        RETURN NULL;
    END IF;

    IF diff = 0 THEN
        RETURN NEW;
    END IF;

    EXECUTE format('SELECT ($1).%I', fk_column_name) 
    USING NEW 
    INTO record_id;

    EXECUTE format('UPDATE %I SET %I = %I + $1 WHERE %I = $2', 
                   target_table, target_column, target_column, fk_column_name)
    USING diff, record_id;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER trg_update_track_listens
AFTER INSERT OR UPDATE ON sae.User_Track_Listening
FOR EACH ROW
EXECUTE FUNCTION update_generic_listens('track_id', 'sae.Track', 'track_listens');

CREATE OR REPLACE TRIGGER trg_update_playlist_listens
AFTER INSERT OR UPDATE ON sae.User_Playlist_Listening
FOR EACH ROW
EXECUTE FUNCTION update_generic_listens('playlist_id', 'sae.Playlist', 'playlist_listens');

CREATE OR REPLACE TRIGGER trg_update_album_listens
AFTER INSERT OR UPDATE ON sae.User_Album_Listening
FOR EACH ROW
EXECUTE FUNCTION update_generic_listens('album_id', 'sae.Album', 'album_listens');

CREATE OR REPLACE FUNCTION update_genre_nb_tracks()
RETURNS TRIGGER AS $$
BEGIN
    IF (TG_OP = 'INSERT') THEN
        UPDATE sae.Genre
        SET genre_nb_tracks = genre_nb_tracks + 1
        WHERE genre_id = NEW.genre_id;
        RETURN NEW;
    ELSIF (TG_OP = 'DELETE') THEN
        UPDATE sae.Genre
        SET genre_nb_tracks = genre_nb_tracks - 1
        WHERE genre_id = OLD.genre_id;
        RETURN OLD;
    END IF;
    RETURN NULL;
END;
$$ language 'plpgsql';

CREATE TRIGGER trg_track_genre_count
AFTER INSERT OR DELETE ON sae.Track_Genre
FOR EACH ROW
EXECUTE PROCEDURE update_genre_nb_tracks();

CREATE OR REPLACE FUNCTION update_parent_genre_tracks()
RETURNS TRIGGER AS $$
DECLARE
  diff INT;
  curr_parent_id INT;
BEGIN
  diff := NEW.genre_nb_tracks - OLD.genre_nb_tracks;
  
  IF diff = 0 THEN RETURN NEW; END IF;

  curr_parent_id := NEW.genre_parent_id;

  WHILE curr_parent_id IS NOT NULL LOOP
    UPDATE sae.Genre
    SET genre_nb_tracks = genre_nb_tracks + diff
    WHERE genre_id = curr_parent_id
    RETURNING genre_parent_id INTO curr_parent_id;
  END LOOP;

  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_update_genre_nb_tracks_parent
AFTER UPDATE ON sae.Genre
FOR EACH ROW
WHEN (OLD.genre_nb_tracks IS DISTINCT FROM NEW.genre_nb_tracks)
EXECUTE FUNCTION update_parent_genre_tracks();

CREATE OR REPLACE FUNCTION majGenresTop(p_user_id INT)
RETURNS void AS $$
BEGIN
    WITH RawScores AS (
        SELECT 
            utl.user_id,
            tg.genre_id,
            (utl.nb_listening * 1) as points 
        FROM sae.User_Track_Listening utl
        JOIN sae.Track_Genre tg ON utl.track_id = tg.track_id
        WHERE utl.user_id = p_user_id
        
        UNION ALL
        
        SELECT 
            utl.user_id,
            tgm.genre_id,
            (utl.nb_listening * 2) as points
        FROM sae.User_Track_Listening utl
        JOIN sae.Track_Genre_Majoritaire tgm ON utl.track_id = tgm.track_id
        WHERE utl.user_id = p_user_id
    ),
    GenreStats AS (
        SELECT 
            user_id,
            genre_id,
            SUM(points) as score_genre,
            SUM(SUM(points)) OVER (PARTITION BY user_id) as total_points_user
        FROM RawScores
        GROUP BY user_id, genre_id
    )
    INSERT INTO sae.Genre_top_User (user_id, genre_id, genre_rate)
    SELECT 
        user_id,
        genre_id,
        ROUND((score_genre::numeric / NULLIF(total_points_user, 0)::numeric), 4)
    FROM GenreStats
    WHERE total_points_user > 0
    ON CONFLICT (user_id, genre_id) 
    DO UPDATE SET 
        genre_rate = EXCLUDED.genre_rate;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION majStats(p_user_id INT)
RETURNS void AS $$
BEGIN
    WITH UserAudioProfile AS (
        SELECT 
            utl.user_id,
            SUM(utl.nb_listening) as total_ecoutes,
            SUM(s.danceability * utl.nb_listening)     / NULLIF(SUM(utl.nb_listening), 0) as avg_dance,
            SUM(s.energy * utl.nb_listening)           / NULLIF(SUM(utl.nb_listening), 0) as avg_energy,
            SUM(s.instrumentalness * utl.nb_listening) / NULLIF(SUM(utl.nb_listening), 0) as avg_instru,
            SUM(s.liveness * utl.nb_listening)         / NULLIF(SUM(utl.nb_listening), 0) as avg_live,
            SUM(s.speechness * utl.nb_listening)       / NULLIF(SUM(utl.nb_listening), 0) as avg_speech,
            SUM(s.tempo * utl.nb_listening)            / NULLIF(SUM(utl.nb_listening), 0) as avg_tempo,
            SUM(s.valence * utl.nb_listening)          / NULLIF(SUM(utl.nb_listening), 0) as avg_valence
        FROM sae.User_Track_Listening utl
        JOIN sae.Stats_echonest s ON utl.track_id = s.track_id
        WHERE utl.user_id = p_user_id
        GROUP BY utl.user_id
    )
    INSERT INTO sae.Stats_user (
        user_id, danceability_affinity, energy_affinity, instrumentalness_affinity, 
        liveness_affinity, speechness_affinity, tempo_affinity, valence_affinity
    )
    SELECT 
        user_id, avg_dance, avg_energy, avg_instru, avg_live, avg_speech, 
        avg_tempo, avg_valence
    FROM UserAudioProfile
    WHERE total_ecoutes > 0
    ON CONFLICT (user_id) DO UPDATE SET 
        danceability_affinity     = EXCLUDED.danceability_affinity,
        energy_affinity           = EXCLUDED.energy_affinity,
        instrumentalness_affinity = EXCLUDED.instrumentalness_affinity,
        liveness_affinity         = EXCLUDED.liveness_affinity,
        speechness_affinity       = EXCLUDED.speechness_affinity,
        tempo_affinity            = EXCLUDED.tempo_affinity,
        valence_affinity          = EXCLUDED.valence_affinity;
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION majPeriod(p_user_id INT)
RETURNS void AS $$
BEGIN
    WITH StatsCalculees AS (
        SELECT 
            l.user_id,
            CASE 
                WHEN EXTRACT(YEAR FROM t.track_date_created) >= 2020 THEN 1
                WHEN EXTRACT(YEAR FROM t.track_date_created) >= 2010 THEN 2
                WHEN EXTRACT(YEAR FROM t.track_date_created) >= 2000 THEN 3
                WHEN EXTRACT(YEAR FROM t.track_date_created) >= 1990 THEN 4
                WHEN EXTRACT(YEAR FROM t.track_date_created) >= 1980 THEN 5
                ELSE 6 
            END as calculated_period_id,
            
            SUM(l.nb_listening) as ecoutes_periode,
            SUM(SUM(l.nb_listening)) OVER (PARTITION BY l.user_id) as ecoutes_totales
        FROM sae.User_Track_Listening l
        JOIN sae.Track t ON l.track_id = t.track_id
        WHERE l.user_id = p_user_id
        GROUP BY l.user_id, calculated_period_id
    )
    INSERT INTO sae.Score_Period (user_id, period_id, affinity_score)
    SELECT 
        user_id,
        calculated_period_id,
        ROUND((ecoutes_periode::numeric / NULLIF(ecoutes_totales, 0)::numeric), 4)
    FROM StatsCalculees
    WHERE ecoutes_totales > 0
    ON CONFLICT (user_id, period_id) 
    DO UPDATE SET affinity_score = EXCLUDED.affinity_score;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION count_favorites_track(function_track_id INT)
RETURNS INT AS $$
DECLARE
    user_favorites_count INT;
    base_track_favorites INT;
BEGIN
    SELECT COUNT(*) INTO user_favorites_count FROM sae.Track_User_Favorite WHERE track_id = function_track_id;
    SELECT track_favorites INTO base_track_favorites FROM sae.Track WHERE track_id = function_track_id;

    RETURN user_favorites_count + COALESCE(base_track_favorites, 0);
END;
$$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION count_favorites_album(function_album_id INT)
RETURNS INT AS $$
DECLARE
    user_favorites_count INT;
    base_album_favorites INT;
BEGIN
    SELECT COUNT(*) INTO user_favorites_count FROM sae.User_Album_Favorite WHERE album_id = function_album_id;
    SELECT album_favorites INTO base_album_favorites FROM sae.Album WHERE album_id = function_album_id;

    RETURN user_favorites_count + COALESCE(base_album_favorites, 0);
END;
$$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION count_favorites_playlist(function_playlist_id INT)
RETURNS INT AS $$
DECLARE
    favorites_count INT;
BEGIN
    SELECT COUNT(*) INTO favorites_count FROM sae.Playlist_User_Favorite WHERE playlist_id = function_playlist_id;
    RETURN favorites_count;
END;
$$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION majHistory() RETURNS trigger AS $$
DECLARE
  history_limit CONSTANT integer := 20; 
  history_count integer;
BEGIN
  SELECT count(*) INTO history_count FROM sae.Search_History WHERE user_id = NEW.user_id;
  
  IF history_count >= history_limit THEN
    DELETE FROM sae.Search_History
    WHERE history_id = (
        SELECT history_id 
        FROM sae.Search_History 
        WHERE user_id = NEW.user_id 
        ORDER BY history_timestamp ASC 
        LIMIT 1
    );
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER tg_majHistory
  BEFORE INSERT ON sae.Search_History
  FOR EACH ROW
  EXECUTE PROCEDURE majHistory();

CREATE OR REPLACE FUNCTION majGlobal() RETURNS trigger AS $$
DECLARE
  v_last_calculated_at TIMESTAMP;
BEGIN
  SELECT last_calculated_at INTO v_last_calculated_at FROM sae.User WHERE user_id = NEW.user_id;

  IF v_last_calculated_at IS NULL OR v_last_calculated_at < (NOW() - INTERVAL '7 days') THEN
    PERFORM majPeriod(NEW.user_id);
    PERFORM majStats(NEW.user_id);
    PERFORM majGenresTop(NEW.user_id);

    UPDATE sae.User SET last_calculated_at = NOW() WHERE user_id = NEW.user_id;
  END IF;

  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER tg_majGlobal
  AFTER INSERT ON sae.User_Track_Listening
  FOR EACH ROW
  EXECUTE PROCEDURE majGlobal();


/*
* =================================================================================
* |-                                    Vues
* =================================================================================
*/

CREATE OR REPLACE VIEW sae.View_User AS
SELECT user_id, pseudo, image, situation_name
FROM sae.User;

DROP MATERIALIZED VIEW IF EXISTS sae.View_Track_Materialise CASCADE;

CREATE MATERIALIZED VIEW sae.View_Track_Materialise AS
SELECT
    t.track_id,
    t.track_title,
    t.track_duration,
    t.track_interest, 
    t.track_comments,
    t.track_date_created,
    t.track_date_recorded,
    t.track_composer,
    t.track_lyricist,
    t.track_publisher,
    t.license_id,
    alb.album_id,
    alb.album_title,
    alb.album_handle,
    alb.album_information,
    alb.album_date_created,
    alb.album_date_released,
    alb.album_engineer,
    alb.album_producer,
    a.artist_id,
    a.artist_name,
    STRING_AGG(DISTINCT tag.tag_name, ', ') AS tags_list,
    STRING_AGG(DISTINCT g.genre_title, ', ') AS genres_list,
    s.danceability,
    s.energy,
    s.tempo,
    STRING_AGG(DISTINCT l.language_name, ', ') AS languages_list
FROM
    sae.Track t
    JOIN sae.Artist_Album_Track aat ON t.track_id = aat.track_id
    JOIN sae.Artist a ON aat.artist_id = a.artist_id
    JOIN sae.Album alb ON aat.album_id = alb.album_id
    LEFT JOIN sae.Stats_echonest s ON t.track_id = s.track_id
    LEFT JOIN sae.Track_Tag tt ON tt.track_id = t.track_id
    LEFT JOIN sae.Tag tag ON tt.tag_id = tag.tag_id
    LEFT JOIN sae.Track_Language tl ON tl.track_id = t.track_id
    LEFT JOIN sae.Language l ON tl.language_id = l.language_id
    LEFT JOIN sae.Track_Genre tg ON t.track_id = tg.track_id
    LEFT JOIN sae.Genre g ON tg.genre_id = g.genre_id
GROUP BY
    t.track_id,
    alb.album_id,
    a.artist_id,
    s.track_id,
    s.danceability, s.energy, s.tempo;

CREATE UNIQUE INDEX idx_view_track_mat_id ON sae.View_Track_Materialise (track_id);



CREATE OR REPLACE VIEW sae.View_Favorite_Listens AS
SELECT
    t.track_id,
    t.track_listens,
    count_favorites_track (t.track_id) as track_favorites,
    alb.album_id,
    alb.album_listens,
    count_favorites_album (alb.album_id) as album_favorites,
    p.playlist_id,
    p.playlist_listens,
    count_favorites_playlist (p.playlist_id) AS playlist_favorites
FROM
    sae.Track t
    JOIN sae.Artist_Album_Track aat ON t.track_id = aat.track_id
    JOIN sae.Album alb ON aat.album_id = alb.album_id
    LEFT JOIN sae.Playlist_Track pt ON pt.track_id = t.track_id
    LEFT JOIN sae.Playlist p ON pt.playlist_id = p.playlist_id
GROUP BY
    t.track_id,
    t.track_listens,
    alb.album_id,
    alb.album_listens,
    p.playlist_id,
    p.playlist_listens;