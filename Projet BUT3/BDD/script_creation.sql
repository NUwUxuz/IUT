/*
 * |- SCRIPT DE CREATION DE LA BASE - SAE 5&6 - EQUIPE 'NoID'
 */

DROP SCHEMA IF EXISTS sae CASCADE;
CREATE SCHEMA sae;
SET SCHEMA 'sae';

/*
* =================================================================================
* |-                            Tables principales
* =================================================================================
*/

create table sae.Language (
    language_id serial primary key,
    language_name varchar(50)
);

create table sae.Album_type (
    type_id serial primary key,
    type_name varchar(50)
);

create table sae.License (
    license_id serial primary key,
    license_name varchar(150)
);

create table sae.Tag (
    tag_id serial primary key,
    tag_name varchar(100)
);

create table sae.Genre (
    genre_id serial primary key,
    genre_parent_id int,
    genre_title varchar(50),
    genre_handle varchar(50),
    genre_nb_tracks int,
    FOREIGN KEY (genre_parent_id) REFERENCES sae.Genre (genre_id) ON DELETE CASCADE
);
CREATE INDEX idx_genre_parent ON sae.Genre(genre_parent_id);

create table sae.Platform (
    platform_id serial primary key,
    platform_name varchar(50)
);

create table sae.Period (
    period_id serial primary key,
    period_interval varchar(50)
);

create table sae.Context (
    context_id serial primary key,
    context_name varchar(50)
);

create table sae.Mood (
    mood_id serial primary key,
    mood_name varchar(50)
);

create table sae.Artist (
    artist_id serial primary key,
    artist_handle varchar(150),
    artist_name varchar(150),
    artist_bio varchar(40000),
    artist_location varchar(500),
    artist_latitude float,
    artist_longitude float,
    artist_members varchar(7000),
    artist_associated_labels varchar(255),
    artist_related_projects varchar(1500),
    artist_active_year_begin int,
    artist_year_end int,
    artist_contact varchar(255),
    artist_favorites int DEFAULT(0),
    artist_comments int DEFAULT(0),
    artist_url varchar(255),
    artist_image_file varchar(255)
);
CREATE INDEX idx_artist_name ON sae.Artist(artist_name);

create table sae.Album (
    album_id serial primary key,
    album_handle varchar(150),
    album_title varchar(150),
    album_information varchar(50000),
    album_date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    album_date_released date,
    album_listens int DEFAULT(0),
    album_favorites int DEFAULT(0),
    album_comments int DEFAULT(0),
    album_producer varchar(255),
    album_engineer varchar(255),
    album_image_file varchar(255),
    album_url varchar(255),
    type_id int,
    FOREIGN KEY (type_id) REFERENCES sae.Album_type (type_id) ON DELETE SET NULL
);
CREATE INDEX idx_album_type ON sae.Album(type_id);
CREATE INDEX idx_album_title ON sae.Album(album_title);

create table sae.Track (
    track_id serial primary key,
    track_title varchar(255),
    track_duration float,
    track_listens int DEFAULT(0),
    track_favorites int DEFAULT(0),
    track_interest float,
    track_comments int DEFAULT(0),
    track_date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    track_date_recorded date,
    track_composer varchar(255),
    track_lyricist varchar(255),
    track_publisher varchar(255),
    license_id int,
    FOREIGN KEY (license_id) REFERENCES sae.License (license_id) ON DELETE SET NULL
);
CREATE INDEX idx_track_license ON sae.Track(license_id);
CREATE INDEX idx_track_title ON sae.Track(track_title);

create table sae.Stats_echonest (
    track_id int primary key,
    acousticness float,
    danceability float,
    energy float,
    instrumentalness float,
    liveness float,
    speechness float,
    tempo float,
    valence float,
    currency int,
    hotness int,
    FOREIGN KEY (track_id) REFERENCES sae.Track (track_id) ON DELETE CASCADE
);

/*
* =================================================================================
* |-                            Utilisateurs et derives
* =================================================================================
*/

create table sae.User (
    user_id serial primary key,
    liked_tracks int DEFAULT(0),
    email varchar(100) NOT NULL UNIQUE,
    image varchar(255),
    pseudo varchar(50),
    user_login varchar(50) NOT NULL UNIQUE,
    user_mdp varchar(64) NOT NULL,
    user_gender char,
    birth_year date,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    situation_name varchar(50),
    frequency_interval varchar(50),
    last_calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

create table sae.Search_History (
    history_id serial primary key,
    history_query varchar(255),
    history_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id int,
    FOREIGN KEY (user_id) REFERENCES sae.User (user_id) ON DELETE CASCADE
);
CREATE INDEX idx_history_user ON sae.Search_History(user_id);

create table sae.Stats_user (
    stat_user_id serial primary key,
    danceability_affinity float,
    energy_affinity float,
    instrumentalness_affinity float,
    liveness_affinity float,
    speechiness_affinity float,
    tempo_affinity float,
    valence_affinity float,
    currency_affinity float,
    hotness_affinity float,
    user_id int,
    FOREIGN KEY (user_id) REFERENCES sae.User (user_id) ON DELETE CASCADE,
    UNIQUE (user_id)
);

create table sae.Playlist (
    playlist_id serial primary key,
    playlist_name varchar(100),
    playlist_listens int default 0,
    user_id int,
    FOREIGN KEY (user_id) REFERENCES sae.User (user_id) ON DELETE CASCADE
);
CREATE INDEX idx_playlist_user ON sae.Playlist(user_id);

/*
* =================================================================================
* |-                        Relations (Tables de liaison)
* =================================================================================
*/

create table sae.Artist_Album_Track (
    artist_id int,
    album_id int,
    track_id int,
    FOREIGN KEY (artist_id) REFERENCES sae.Artist (artist_id) ON DELETE CASCADE,
    FOREIGN KEY (album_id) REFERENCES sae.Album (album_id) ON DELETE CASCADE,
    FOREIGN KEY (track_id) REFERENCES sae.Track (track_id) ON DELETE CASCADE,
    primary key (artist_id, album_id, track_id)
);
CREATE INDEX idx_aat_album ON sae.Artist_Album_Track(album_id);
CREATE INDEX idx_aat_track ON sae.Artist_Album_Track(track_id);

create table sae.Playlist_User_Favorite (
    user_id INT,
    playlist_id INT,
    added_at date DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES sae.User (user_id) ON DELETE CASCADE,
    FOREIGN KEY (playlist_id) REFERENCES sae.Playlist (playlist_id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, playlist_id)
);
CREATE INDEX idx_puf_playlist ON sae.Playlist_User_Favorite(playlist_id);

create table sae.Playlist_User (
    user_id INT,
    playlist_id INT,
    FOREIGN KEY (user_id) REFERENCES sae.User (user_id) ON DELETE CASCADE,
    FOREIGN KEY (playlist_id) REFERENCES sae.Playlist (playlist_id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, playlist_id)
);
CREATE INDEX idx_pu_playlist ON sae.Playlist_User(playlist_id);

create table sae.Playlist_Track (
    playlist_id int,
    track_id int,
    PRIMARY KEY (playlist_id, track_id),
    FOREIGN KEY (playlist_id) REFERENCES sae.Playlist (playlist_id) ON DELETE CASCADE,
    FOREIGN KEY (track_id) REFERENCES sae.Track (track_id) ON DELETE CASCADE
);
CREATE INDEX idx_pt_track ON sae.Playlist_Track(track_id);

create table sae.User_Context (
    user_id int,
    context_id int,
    FOREIGN KEY (user_id) REFERENCES sae.User (user_id) ON DELETE CASCADE,
    FOREIGN KEY (context_id) REFERENCES sae.Context (context_id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, context_id)
);
CREATE INDEX idx_uc_context ON sae.User_Context(context_id);

create table sae.Score_Mood (
    user_id int,
    mood_id int,
    affinity_score float DEFAULT(0),
    FOREIGN KEY (user_id) REFERENCES sae.User (user_id) ON DELETE CASCADE,
    FOREIGN KEY (mood_id) REFERENCES sae.Mood (mood_id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, mood_id)
);
CREATE INDEX idx_sm_mood ON sae.Score_Mood(mood_id);

create table sae.User_Platform (
    user_id int,
    platform_id int,
    FOREIGN KEY (user_id) REFERENCES sae.User (user_id) ON DELETE CASCADE,
    FOREIGN KEY (platform_id) REFERENCES sae.Platform (platform_id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, platform_id)
);
CREATE INDEX idx_up_platform ON sae.User_Platform(platform_id);

create table sae.Score_Period (
    user_id int,
    period_id int,
    affinity_score float DEFAULT(0),
    FOREIGN KEY (user_id) REFERENCES sae.User (user_id) ON DELETE CASCADE,
    FOREIGN KEY (period_id) REFERENCES sae.Period (period_id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, period_id)
);
CREATE INDEX idx_sp_period ON sae.Score_Period(period_id);

create table sae.User_Track_Listening (
    user_id int,
    track_id int,
    nb_listening int DEFAULT(1),
    FOREIGN KEY (user_id) REFERENCES sae.User (user_id) ON DELETE CASCADE,
    FOREIGN KEY (track_id) REFERENCES sae.Track (track_id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, track_id)
);
CREATE INDEX idx_utl_track ON sae.User_Track_Listening(track_id);

create table sae.Track_User_Favorite (
    user_id int,
    track_id int,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES sae.User (user_id) ON DELETE CASCADE,
    FOREIGN KEY (track_id) REFERENCES sae.Track (track_id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, track_id)
);
CREATE INDEX idx_tuf_track ON sae.Track_User_Favorite(track_id);

create table sae.Track_Genre (
    track_id int,
    genre_id int,
    FOREIGN KEY (track_id) REFERENCES sae.Track (track_id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES sae.Genre (genre_id) ON DELETE CASCADE,
    PRIMARY KEY (track_id, genre_id)
);
CREATE INDEX idx_tg_genre ON sae.Track_Genre(genre_id);

create table sae.Track_Genre_Majoritaire (
    track_id int,
    genre_id int,
    FOREIGN KEY (track_id) REFERENCES sae.Track (track_id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES sae.Genre (genre_id) ON DELETE CASCADE,
    PRIMARY KEY (track_id, genre_id)
);
CREATE INDEX idx_tgm_genre ON sae.Track_Genre_Majoritaire(genre_id);

create table sae.Genre_top_User (
    user_id int,
    genre_id int,
    genre_rate float,
    FOREIGN KEY (user_id) REFERENCES sae.User (user_id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES sae.Genre (genre_id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, genre_id)
);
CREATE INDEX idx_gtu_genre ON sae.Genre_top_User(genre_id);

create table sae.Track_Language (
    track_id int,
    language_id int,
    FOREIGN KEY (track_id) REFERENCES sae.Track (track_id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES sae.Language (language_id) ON DELETE CASCADE,
    PRIMARY KEY (track_id, language_id)
);
CREATE INDEX idx_tl_language ON sae.Track_Language(language_id);

create table sae.Album_Tag (
    album_id int,
    tag_id int,
    FOREIGN KEY (album_id) REFERENCES sae.Album (album_id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES sae.Tag (tag_id) ON DELETE CASCADE,
    PRIMARY KEY (album_id, tag_id)
);
CREATE INDEX idx_at_tag ON sae.Album_Tag(tag_id);

create table sae.Track_Tag(
    track_id int,
    tag_id int,
    FOREIGN KEY (track_id) REFERENCES sae.Track (track_id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES sae.Tag (tag_id) ON DELETE CASCADE,
    PRIMARY KEY (track_id, tag_id)
);
CREATE INDEX idx_tt_tag ON sae.Track_Tag(tag_id);

create table sae.Artist_Tag(
    artist_id int,
    tag_id int,
    FOREIGN KEY (artist_id) REFERENCES sae.Artist (artist_id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES sae.Tag (tag_id) ON DELETE CASCADE,
    PRIMARY KEY (artist_id, tag_id)
);
CREATE INDEX idx_art_tag ON sae.Artist_Tag(tag_id);

create table sae.User_Artist_Favorite(
    artist_id int,
    user_id int,
    FOREIGN KEY (artist_id) REFERENCES sae.Artist (artist_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES sae.User (user_id) ON DELETE CASCADE,
    PRIMARY KEY (artist_id, user_id)
);

CREATE INDEX idx_uaf_user ON sae.User_Artist_Favorite(user_id);

create table sae.Artist_Language (
    artist_id int,
    language_id int,
    FOREIGN KEY (artist_id) REFERENCES sae.Artist (artist_id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES sae.Language (language_id) ON DELETE CASCADE,
    PRIMARY KEY (artist_id, language_id)
);
CREATE INDEX idx_al_language ON sae.Artist_Language(language_id);

CREATE TABLE sae.Listening_History (
    history_id   SERIAL PRIMARY KEY,
    user_id      INT,
    playlist_id  INT,
    listened_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES sae.User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (playlist_id) REFERENCES sae.Playlist(playlist_id) ON DELETE CASCADE
);
CREATE INDEX idx_lh_user ON sae.Listening_History(user_id);
CREATE INDEX idx_lh_playlist ON sae.Listening_History(playlist_id);

create table sae.User_Album_Favorite (
    user_id int,
    album_id int,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES sae.User (user_id) ON DELETE CASCADE,
    FOREIGN KEY (album_id) REFERENCES sae.Album (album_id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, album_id)
);
CREATE INDEX idx_uaf_album ON sae.User_Album_Favorite(album_id);

create table sae.User_Album_Listening (
    user_id int,
    album_id int,
    nb_listening int,
    FOREIGN KEY (user_id) REFERENCES sae.User (user_id) ON DELETE CASCADE,
    FOREIGN KEY (album_id) REFERENCES sae.Album (album_id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, album_id)
);
CREATE INDEX idx_ual_album ON sae.User_Album_Listening(album_id);

create table sae.User_Playlist_Listening (
    user_id int,
    playlist_id int,
    nb_listening int,
    FOREIGN KEY (user_id) REFERENCES sae.User (user_id) ON DELETE CASCADE,
    FOREIGN KEY (playlist_id) REFERENCES sae.Playlist (playlist_id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, playlist_id)
);
CREATE INDEX idx_upl_playlist ON sae.User_Playlist_Listening(playlist_id);