----------------------------
-- PEUPLEMENT DE LA BASE
----------------------------

----------------------------
-- Charger User
----------------------------

CREATE OR REPLACE FUNCTION charger_users(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.User (email, user_login, user_mdp, user_gender, birth_year, situation_name, frequency_interval)
         FROM %L
         WITH (FORMAT csv, HEADER true, DELIMITER '','')',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_users('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data_perso/final/user.csv');

----------------------------
-- Charger Platforms
----------------------------

CREATE OR REPLACE FUNCTION charger_platforms(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Platform(platform_name) FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_platforms('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data_perso/final/platform.csv');

----------------------------
-- Charger User_Platform
----------------------------

CREATE OR REPLACE FUNCTION charger_user_platform(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.User_Platform(user_id, platform_id) FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_user_platform('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data_perso/final/user_platform.csv');

----------------------------
-- Charger Moods
----------------------------

CREATE OR REPLACE FUNCTION charger_moods(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Mood(mood_name) FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_moods('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data_perso/final/mood.csv');

----------------------------
-- Charger Score_Mood
----------------------------

CREATE OR REPLACE FUNCTION charger_score_mood(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Score_Mood(user_id, mood_id) FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_score_mood('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data_perso/final/score_mood.csv');

----------------------------
-- Charger Contexts
----------------------------

CREATE OR REPLACE FUNCTION charger_contexts(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Context(context_name) FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_contexts('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data_perso/final/context.csv');

----------------------------
-- Charger User_Context
----------------------------

CREATE OR REPLACE FUNCTION charger_user_context(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.User_Context(user_id, context_id) FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_user_context('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data_perso/final/user_context.csv');

----------------------------
-- Charger Periods
----------------------------

CREATE OR REPLACE FUNCTION charger_periods(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Period(period_interval) FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_periods('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data_perso/final/period.csv');

----------------------------
-- Charger Score_Period
----------------------------

CREATE OR REPLACE FUNCTION charger_score_period(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Score_Period(user_id, period_id) FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_score_period('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data_perso/final/score_period.csv');


----------------------------
-- Charger Language
----------------------------

CREATE OR REPLACE FUNCTION charger_language(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Language(language_id, language_name)
         FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_language('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data/output_tables/language.csv');


-------------------------------------
-- Charger Album_Type
-------------------------------------

CREATE OR REPLACE FUNCTION charger_album_type(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Album_Type(type_id, type_name)
         FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_album_type('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data/output_tables/album_type.csv');

-------------------------------------
-- Charger License
-------------------------------------

CREATE OR REPLACE FUNCTION charger_license(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.License(license_id, license_name)
         FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_license('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data/output_tables/license.csv');

-------------------------------------
-- Charger Tag
-------------------------------------

CREATE OR REPLACE FUNCTION charger_tag(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Tag(tag_id, tag_name)
         FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_tag('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data/output_tables/tag.csv');

-------------------------------------
-- Charger Genre
-------------------------------------

CREATE OR REPLACE FUNCTION charger_genre(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Genre(genre_id, genre_parent_id, genre_title, genre_handle)
         FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_genre('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data/output_tables/genre_table_cleaned.csv');

-------------------------------------
-- Charger Artist
-------------------------------------

CREATE OR REPLACE FUNCTION charger_artist(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Artist(
            artist_id, artist_handle, artist_name, artist_bio,
            artist_location, artist_latitude, artist_longitude,
            artist_members, artist_associated_labels, artist_related_projects,
            artist_active_year_begin, artist_year_end,
            artist_contact, artist_url, artist_image_file
        )
        FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_artist('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data/output_tables/artist_table_cleaned.csv');

-------------------------------------
-- Charger Album
-------------------------------------

CREATE OR REPLACE FUNCTION charger_album(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Album(
            album_id, album_handle, album_title, album_information,
            album_date_created, album_date_released,
            album_producer, album_engineer, album_image_file,
            album_url, type_id
        )
        FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_album('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data/output_tables/album_table_cleaned.csv');

-------------------------------------
-- Charger Track
-------------------------------------

CREATE OR REPLACE FUNCTION charger_track(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Track(
            track_id, track_title, track_duration,
            track_interest, track_date_recorded,
            track_composer, track_lyricist, track_publisher,
            license_id
        )
        FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_track('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data/output_tables/track_table_cleaned.csv');

-------------------------------------
-- Charger Stats_Echonest
-------------------------------------

CREATE OR REPLACE FUNCTION charger_stats_echonest(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Stats_echonest(
            track_id, acousticness, danceability, energy,
            instrumentalness, liveness, speechness,
            tempo, valence, currency, hotness
        )
        FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_stats_echonest('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data/output_tables/stats_echonest_cleaned.csv');

-------------------------------------
-- Charger Artist_Album_Track
-------------------------------------

CREATE OR REPLACE FUNCTION charger_artist_album_track(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Artist_Album_Track(artist_id, album_id, track_id)
         FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_artist_album_track('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data/output_tables/artist_album_track_cleaned.csv');

-------------------------------------
-- Charger Track_Genre
-------------------------------------

CREATE OR REPLACE FUNCTION charger_track_genre(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Track_Genre(track_id, genre_id)
         FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_track_genre('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data/output_tables/track_genre_cleaned.csv');

-------------------------------------
-- Charger Track_Genre_Majoritaire
-------------------------------------

CREATE OR REPLACE FUNCTION charger_track_genre_majoritaire(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Track_Genre_Majoritaire(track_id, genre_id)
         FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_track_genre_majoritaire('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data/output_tables/track_genre_majoritaire_cleaned.csv');

-------------------------------------
-- Charger Track_Language
-------------------------------------

CREATE OR REPLACE FUNCTION charger_track_language(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Track_Language(track_id, language_id)
         FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_track_language('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data/output_tables/track_language_cleaned.csv');

-------------------------------------
-- Charger Album_Tag
-------------------------------------

CREATE OR REPLACE FUNCTION charger_album_tag(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Album_Tag(album_id, tag_id)
         FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_album_tag('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data/output_tables/album_tag_cleaned.csv');

-------------------------------------
-- Charger Artist_Tag
-------------------------------------

CREATE OR REPLACE FUNCTION charger_artist_tag(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Artist_Tag(artist_id, tag_id)
         FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_artist_tag('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data/output_tables/artist_tag_cleaned.csv');


-------------------------------------
-- Charger Track_Tag
-------------------------------------

CREATE OR REPLACE FUNCTION charger_track_tag(chemin_csv TEXT)
RETURNS void AS $$
BEGIN
    EXECUTE format(
        'COPY sae.Track_Tag(track_id, tag_id)
         FROM %L WITH (FORMAT csv, HEADER true)',
        chemin_csv
    );
END;
$$ LANGUAGE plpgsql;

SELECT charger_track_tag('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data/output_tables/track_tag_cleaned.csv');

-------------------------------------
-- Charger User_Artist_Favorite
-------------------------------------

-- CREATE OR REPLACE FUNCTION charger_user_artist_favorite(chemin_csv TEXT)
-- RETURNS void AS $$
-- BEGIN
--     EXECUTE format(
--         'COPY sae.User_Artist_Favorite(artist_id, user_id)
--          FROM %L WITH (FORMAT csv, HEADER true)',
--         chemin_csv
--     );
-- END;
-- $$ LANGUAGE plpgsql;

-- SELECT charger_user_artist_favorite('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data_perso/final/user_artist_favorite.csv');

-- ----------------------------
-- -- Charger Track_User_favorite
-- ----------------------------

-- CREATE OR REPLACE FUNCTION charger_track_user_favorite(chemin_csv TEXT)
-- RETURNS void AS $$
-- BEGIN
--     EXECUTE format(
--         'COPY sae.Track_User_favorite(user_id, track_id) FROM %L WITH (FORMAT csv, HEADER true)',
--         chemin_csv
--     );
-- END;
-- $$ LANGUAGE plpgsql;
-- SELECT charger_moods('C:/Users/amazi/OneDrive/Documents/IUT/Semestre_5/Code_Sae/data_perso/final/track_user_favorite.csv');