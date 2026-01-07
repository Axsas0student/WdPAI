CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    bio TEXT,
    enabled BOOLEAN DEFAULT TRUE
);

INSERT INTO users (firstname, lastname, email, password, bio, enabled)
VALUES (
    'Jan',
    'Kowalski',
    'jan.kowalski@example.com',
    '$2b$10$ZbzQrqD1vDhLJpYe/vzSbeDJHTUnVPCpwlXclkiFa8dO5gOAfg8tq',
    'Lubi programować w JS i PL/SQL.',
    TRUE
);

-- HistorIQ: topics
CREATE TABLE IF NOT EXISTS topics (
    id SERIAL PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    icon VARCHAR(40) NOT NULL DEFAULT 'book',
    is_locked BOOLEAN NOT NULL DEFAULT FALSE,
    progress INTEGER NOT NULL DEFAULT 0,
    sort_order INTEGER NOT NULL DEFAULT 0
);

INSERT INTO topics (name, icon, is_locked, progress, sort_order) VALUES
('Ancient Rome', 'laurel', FALSE, 15, 1),
('Ancient Egypt', 'pyramid', FALSE, 50, 2),
('The Viking Age', 'ship', FALSE, 0, 3),
('Persian Empire', 'shield', FALSE, 0, 4),
('World War II', 'rocket', FALSE, 75, 5),
('The Renaissance', 'palette', TRUE, 0, 6),
('Industrial Age', 'gear', TRUE, 0, 7),
('Medieval Times', 'castle', FALSE, 10, 8)
ON CONFLICT DO NOTHING;

-- HistorIQ: questions + answers (ABCD)

CREATE TABLE IF NOT EXISTS questions (
    id SERIAL PRIMARY KEY,
    topic_id INTEGER NOT NULL REFERENCES topics(id) ON DELETE CASCADE,
    content TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS answers (
    id SERIAL PRIMARY KEY,
    question_id INTEGER NOT NULL REFERENCES questions(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    is_correct BOOLEAN NOT NULL DEFAULT FALSE
);

-- Seed: Ancient Rome (topic sort_order=1)
-- Uwaga: zakładamy, że Ancient Rome ma id=1 (jeśli u Ciebie id są inne, zrób SELECT i popraw topic_id)
INSERT INTO questions (topic_id, content) VALUES
(1, 'Who was the first emperor of Rome?'),
(1, 'In which year was Julius Caesar assassinated?')
ON CONFLICT DO NOTHING;

-- Answers for Q1 (first emperor)
INSERT INTO answers (question_id, content, is_correct) VALUES
((SELECT id FROM questions WHERE topic_id=1 AND content='Who was the first emperor of Rome?' LIMIT 1), 'Julius Caesar', FALSE),
((SELECT id FROM questions WHERE topic_id=1 AND content='Who was the first emperor of Rome?' LIMIT 1), 'Augustus', TRUE),
((SELECT id FROM questions WHERE topic_id=1 AND content='Who was the first emperor of Rome?' LIMIT 1), 'Nero', FALSE),
((SELECT id FROM questions WHERE topic_id=1 AND content='Who was the first emperor of Rome?' LIMIT 1), 'Trajan', FALSE)
ON CONFLICT DO NOTHING;

-- Answers for Q2 (assassination year)
INSERT INTO answers (question_id, content, is_correct) VALUES
((SELECT id FROM questions WHERE topic_id=1 AND content='In which year was Julius Caesar assassinated?' LIMIT 1), '44 BC', TRUE),
((SELECT id FROM questions WHERE topic_id=1 AND content='In which year was Julius Caesar assassinated?' LIMIT 1), '27 BC', FALSE),
((SELECT id FROM questions WHERE topic_id=1 AND content='In which year was Julius Caesar assassinated?' LIMIT 1), '14 AD', FALSE),
((SELECT id FROM questions WHERE topic_id=1 AND content='In which year was Julius Caesar assassinated?' LIMIT 1), '79 AD', FALSE)
ON CONFLICT DO NOTHING;

-- HistorIQ: zapis wyników
CREATE TABLE IF NOT EXISTS quiz_attempts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    topic_id INTEGER NOT NULL REFERENCES topics(id) ON DELETE CASCADE,
    score INTEGER NOT NULL,
    total INTEGER NOT NULL,
    xp INTEGER NOT NULL DEFAULT 0,
    finished_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- HistorIQ: progres per user per topic
CREATE TABLE IF NOT EXISTS user_topic_progress (
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    topic_id INTEGER NOT NULL REFERENCES topics(id) ON DELETE CASCADE,
    progress INTEGER NOT NULL DEFAULT 0,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    PRIMARY KEY (user_id, topic_id)
);
