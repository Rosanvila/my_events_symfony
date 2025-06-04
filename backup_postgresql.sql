-- PostgreSQL dump
-- Database: my_events

SET statement_timeout = 0;

SET lock_timeout = 0;

SET client_encoding = 'UTF8';

SET standard_conforming_strings = on;

SET check_function_bodies = false;

SET client_min_messages = warning;

-- Suppression des tables existantes (dans l'ordre inverse des dépendances)
DROP TABLE IF EXISTS reset_password_request;

DROP TABLE IF EXISTS payment;

DROP TABLE IF EXISTS participation;

DROP TABLE IF EXISTS oauth_connection;

DROP TABLE IF EXISTS messenger_messages;

DROP TABLE IF EXISTS event;

DROP TABLE IF EXISTS doctrine_migration_versions;

DROP TABLE IF EXISTS category;

DROP TABLE IF EXISTS "user";

-- Création des tables (dans l'ordre des dépendances)
CREATE TABLE "user" (
    id SERIAL PRIMARY KEY,
    email VARCHAR(180) NOT NULL,
    roles JSON NOT NULL,
    password VARCHAR(255) NOT NULL,
    firstname VARCHAR(255) NOT NULL,
    lastname VARCHAR(255) NOT NULL,
    is_verified BOOLEAN NOT NULL,
    created_at TIMESTAMP NOT NULL,
    photo VARCHAR(255)
);

CREATE TABLE category (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description VARCHAR(255)
);

CREATE TABLE doctrine_migration_versions (
    version VARCHAR(191) PRIMARY KEY,
    executed_at TIMESTAMP,
    execution_time INTEGER
);

CREATE TABLE event (
    id SERIAL PRIMARY KEY,
    organizer_id INTEGER NOT NULL,
    category_id INTEGER NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    photo TEXT,
    max_participants INTEGER NOT NULL,
    is_paid BOOLEAN NOT NULL,
    created_at TIMESTAMP NOT NULL,
    price DECIMAL(10, 2),
    start_date TIMESTAMP NOT NULL,
    end_date TIMESTAMP NOT NULL,
    location VARCHAR(255) NOT NULL,
    current_state VARCHAR(55) NOT NULL,
    CONSTRAINT FK_3BAE0AA712469DE2 FOREIGN KEY (category_id) REFERENCES category (id),
    CONSTRAINT FK_3BAE0AA7876C4DDA FOREIGN KEY (organizer_id) REFERENCES "user" (id)
);

CREATE TABLE messenger_messages (
    id BIGSERIAL PRIMARY KEY,
    body TEXT NOT NULL,
    headers TEXT NOT NULL,
    queue_name VARCHAR(190) NOT NULL,
    created_at TIMESTAMP NOT NULL,
    available_at TIMESTAMP NOT NULL,
    delivered_at TIMESTAMP
);

CREATE TABLE oauth_connection (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    provider VARCHAR(50) NOT NULL,
    provider_id VARCHAR(255) NOT NULL,
    email VARCHAR(180) NOT NULL,
    created_at TIMESTAMP NOT NULL,
    CONSTRAINT FK_BB31DCDDA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id)
);

CREATE TABLE participation (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    event_id INTEGER NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP NOT NULL,
    CONSTRAINT FK_AB55E24F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id),
    CONSTRAINT FK_AB55E24FA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id)
);

CREATE TABLE payment (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    event_id INTEGER NOT NULL,
    stripe_session_id VARCHAR(255) NOT NULL,
    amount VARCHAR(255) NOT NULL,
    currency VARCHAR(3) NOT NULL,
    status VARCHAR(20) NOT NULL,
    created_at TIMESTAMP NOT NULL,
    stripe_payment_intent_id VARCHAR(255) NOT NULL,
    CONSTRAINT FK_6D28840D71F7E88B FOREIGN KEY (event_id) REFERENCES event (id),
    CONSTRAINT FK_6D28840DA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id)
);

CREATE TABLE reset_password_request (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    selector VARCHAR(100) NOT NULL,
    hashed_token VARCHAR(100) NOT NULL,
    requested_at TIMESTAMP NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id)
);

-- Création des index
CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email);

CREATE INDEX IDX_3BAE0AA7876C4DDA ON event (organizer_id);

CREATE INDEX IDX_3BAE0AA712469DE2 ON event (category_id);

CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name);

CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at);

CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at);

CREATE INDEX IDX_BB31DCDDA76ED395 ON oauth_connection (user_id);

CREATE INDEX IDX_AB55E24FA76ED395 ON participation (user_id);

CREATE INDEX IDX_AB55E24F71F7E88B ON participation (event_id);

CREATE INDEX IDX_6D28840DA76ED395 ON payment (user_id);

CREATE INDEX IDX_6D28840D71F7E88B ON payment (event_id);

CREATE INDEX IDX_7CE748AA76ED395 ON reset_password_request (user_id);

-- Insertion des données
INSERT INTO
    "user" (
        id,
        email,
        roles,
        password,
        firstname,
        lastname,
        is_verified,
        created_at,
        photo
    )
VALUES (
        1,
        'robin.sa28@gmail.com',
        '["ROLE_USER"]',
        '$2y$13$H6xPxYKzqXzqXzqXzqXzqOqXzqXzqXzqXzqXzqXzqXzqXzqXzq',
        'Robin',
        'SA',
        true,
        '2025-04-03 17:35:38',
        NULL
    ),
    (
        2,
        'rosanvila28@gmail.com',
        '["ROLE_USER"]',
        '$2y$13$H6xPxYKzqXzqXzqXzqXzqOqXzqXzqXzqXzqXzqXzqXzqXzqXzq',
        'Rosa',
        'Nvila',
        true,
        '2025-04-03 17:35:38',
        NULL
    );

INSERT INTO
    category (id, name, description)
VALUES (
        1,
        'Concert',
        'Événements musicaux avec des artistes en live'
    ),
    (
        2,
        'Festival',
        'Rassemblements culturels et artistiques sur plusieurs jours'
    ),
    (
        3,
        'Conférence',
        'Rencontres professionnelles ou éducatives sur un thème donné'
    ),
    (
        4,
        'Sport',
        'Compétitions et activités sportives organisées'
    ),
    (
        5,
        'Soirée',
        'Fêtes et rassemblements festifs nocturnes'
    ),
    (
        6,
        'Exposition',
        'Présentations artistiques ou culturelles dans un lieu dédié'
    ),
    (
        7,
        'Atelier',
        'Sessions de formation ou de création interactives'
    ),
    (
        8,
        'Meetup',
        'Rencontres informelles entre passionnés d''un sujet'
    ),
    (
        9,
        'Projection',
        'Diffusions de films, documentaires ou courts-métrages'
    ),
    (
        10,
        'Gaming',
        'Événements autour du jeu vidéo, LAN parties et tournois'
    );

INSERT INTO
    doctrine_migration_versions (
        version,
        executed_at,
        execution_time
    )
VALUES (
        'DoctrineMigrations\\Version20250403173532',
        '2025-04-03 17:35:38',
        405
    ),
    (
        'DoctrineMigrations\\Version20250403173533',
        '2025-05-27 15:40:49',
        47
    ),
    (
        'DoctrineMigrations\\Version20250407135629',
        '2025-04-07 13:56:39',
        49
    ),
    (
        'DoctrineMigrations\\Version20250407135630',
        '2025-05-04 16:27:11',
        74
    ),
    (
        'DoctrineMigrations\\Version20250527142922',
        '2025-05-27 14:29:34',
        57
    ),
    (
        'DoctrineMigrations\\Version20250527162231',
        '2025-05-27 16:22:55',
        9
    );

INSERT INTO
    oauth_connection (
        id,
        user_id,
        provider,
        provider_id,
        email,
        created_at
    )
VALUES (
        1,
        1,
        'google',
        '102160246750737953745',
        'robin.sa28@gmail.com',
        '2025-04-03 17:36:04'
    ),
    (
        2,
        2,
        'google',
        '113581035052792767226',
        'rosanvila28@gmail.com',
        '2025-04-03 17:37:34'
    ),
    (
        3,
        1,
        'facebook',
        '10236232844631489',
        'robin.sa28@gmail.com',
        '2025-05-04 16:15:22'
    );

INSERT INTO
    participation (
        id,
        user_id,
        event_id,
        status,
        created_at
    )
VALUES (
        20,
        2,
        13,
        'confirmed',
        '2025-05-26 15:03:53'
    ),
    (
        22,
        1,
        13,
        'confirmed',
        '2025-05-26 15:21:59'
    ),
    (
        23,
        1,
        39,
        'confirmed',
        '2025-06-02 15:04:55'
    ),
    (
        24,
        1,
        53,
        'confirmed',
        '2025-06-02 15:06:20'
    ),
    (
        25,
        1,
        38,
        'confirmed',
        '2025-06-02 15:09:22'
    ),
    (
        26,
        1,
        45,
        'confirmed',
        '2025-06-02 15:13:42'
    );

INSERT INTO
    payment (
        id,
        user_id,
        event_id,
        stripe_session_id,
        amount,
        currency,
        status,
        created_at,
        stripe_payment_intent_id
    )
VALUES (
        18,
        2,
        13,
        'cs_test_a1kRCVTgChEgR6PG8Sgn8kuALSDStBLkrPqUOCN4KWC8jvJ7qNy40EU1hm',
        '1.00',
        'eur',
        'completed',
        '2025-05-26 15:03:53',
        'pi_3RT2Z6EAw9iEpEqK1rsnArcG'
    ),
    (
        20,
        1,
        13,
        'cs_test_a1LVYJEVpcFRHFtX9WNEnDnOSYjXJc31YZQ63qA243wwPknPY0PukMZbRx',
        '1.00',
        'eur',
        'completed',
        '2025-05-26 15:21:59',
        'pi_3RT2qcEAw9iEpEqK0K8WECNF'
    ),
    (
        21,
        1,
        53,
        'cs_test_a1aA73sbyT8IEUhhCtMQuGwg03Lo8y9uAB7rpFvmLcdSYf6gDWL31KWSgE',
        '6.00',
        'eur',
        'completed',
        '2025-06-02 15:06:20',
        'pi_3RVZwKEAw9iEpEqK0bJLolE2'
    ),
    (
        22,
        1,
        38,
        'cs_test_a1I6JHJw19PEWc3tHL4PxkxuFtYctNkG4Smz6plOEEUsSlqjI9nVggunNe',
        '64.47',
        'eur',
        'completed',
        '2025-06-02 15:09:22',
        'pi_3RVZzFEAw9iEpEqK1BY1vZxt'
    ),
    (
        23,
        1,
        45,
        'cs_test_a1zE4VEc3Wq33mDnAzyGQCSy6pvClmVf7ejsj3ne7FDcfPqA6IfoaIkS8f',
        '35.38',
        'eur',
        'completed',
        '2025-06-02 15:13:42',
        'pi_3RVa3SEAw9iEpEqK1znYIB2f'
    );

-- Réinitialisation de la séquence user
SELECT setval ( 'user_id_seq', ( SELECT MAX(id) FROM "user" ) );

-- Réinitialisation de la séquence category
SELECT setval (
        'category_id_seq', (
            SELECT MAX(id)
            FROM category
        )
    );

-- Réinitialisation de la séquence doctrine_migration_versions
SELECT setval (
        'doctrine_migration_versions_version_seq', (
            SELECT MAX(id)
            FROM doctrine_migration_versions
        )
    );

-- Réinitialisation de la séquence oauth_connection
SELECT setval (
        'oauth_connection_id_seq', (
            SELECT MAX(id)
            FROM oauth_connection
        )
    );

-- Réinitialisation de la séquence participation
SELECT setval (
        'participation_id_seq', (
            SELECT MAX(id)
            FROM participation
        )
    );

-- Réinitialisation de la séquence payment
SELECT setval ( 'payment_id_seq', ( SELECT MAX(id) FROM payment ) );

-- Réinitialisation des séquences pour les tables vides
SELECT setval ('event_id_seq', 1);

SELECT setval ( 'messenger_messages_id_seq', 1 );

-- Création de la séquence si elle n'existe pas
DO $$ BEGIN IF NOT EXISTS (
    SELECT 1
    FROM pg_class
    WHERE
        relname = 'reset_password_request_id_seq'
) THEN CREATE SEQUENCE reset_password_request_id_seq;

END IF;

END $$;

-- Réinitialisation de la séquence
SELECT setval (
        'reset_password_request_id_seq', 1, false
    );