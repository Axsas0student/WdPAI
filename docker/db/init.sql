CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    bio TEXT,
    enabled BOOLEAN DEFAULT TRUE,
    is_admin BOOLEAN NOT NULL DEFAULT FALSE
);

INSERT INTO users (firstname, lastname, email, password, bio, enabled, is_admin)
VALUES (
    'Jan',
    'Kowalski',
    'jan.kowalski@example.com',
    '$2b$10$ZbzQrqD1vDhLJpYe/vzSbeDJHTUnVPCpwlXclkiFa8dO5gOAfg8tq',
    'Lubi programować w JS i PL/SQL.',
    TRUE
)
ON CONFLICT (email) DO NOTHING;

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

-- Seed: Ancient Rome (zakładamy topic_id=1 przy świeżej bazie)
INSERT INTO questions (topic_id, content) VALUES
(1, 'Who was the first emperor of Rome?'),
(1, 'In which year was Julius Caesar assassinated?')
ON CONFLICT DO NOTHING;

-- Answers for Q1
INSERT INTO answers (question_id, content, is_correct) VALUES
((SELECT id FROM questions WHERE topic_id=1 AND content='Who was the first emperor of Rome?' LIMIT 1), 'Julius Caesar', FALSE),
((SELECT id FROM questions WHERE topic_id=1 AND content='Who was the first emperor of Rome?' LIMIT 1), 'Augustus', TRUE),
((SELECT id FROM questions WHERE topic_id=1 AND content='Who was the first emperor of Rome?' LIMIT 1), 'Nero', FALSE),
((SELECT id FROM questions WHERE topic_id=1 AND content='Who was the first emperor of Rome?' LIMIT 1), 'Trajan', FALSE)
ON CONFLICT DO NOTHING;

-- Answers for Q2
INSERT INTO answers (question_id, content, is_correct) VALUES
((SELECT id FROM questions WHERE topic_id=1 AND content='In which year was Julius Caesar assassinated?' LIMIT 1), '44 BC', TRUE),
((SELECT id FROM questions WHERE topic_id=1 AND content='In which year was Julius Caesar assassinated?' LIMIT 1), '27 BC', FALSE),
((SELECT id FROM questions WHERE topic_id=1 AND content='In which year was Julius Caesar assassinated?' LIMIT 1), '14 AD', FALSE),
((SELECT id FROM questions WHERE topic_id=1 AND content='In which year was Julius Caesar assassinated?' LIMIT 1), '79 AD', FALSE)
ON CONFLICT DO NOTHING;

CREATE TABLE IF NOT EXISTS quiz_attempts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    topic_id INTEGER NOT NULL REFERENCES topics(id) ON DELETE CASCADE,
    score INTEGER NOT NULL,
    total INTEGER NOT NULL,
    xp INTEGER NOT NULL DEFAULT 0,
    finished_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS user_topic_progress (
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    topic_id INTEGER NOT NULL REFERENCES topics(id) ON DELETE CASCADE,
    progress INTEGER NOT NULL DEFAULT 0,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    PRIMARY KEY (user_id, topic_id)
);

-- VIEW: szybkie statystyki per user (xp + completed)
CREATE OR REPLACE VIEW v_user_stats AS
SELECT
    user_id,
    COALESCE(SUM(xp), 0)::int AS xp,
    COUNT(*)::int AS completed
FROM quiz_attempts
GROUP BY user_id;

-- FUNCTION: zapis próby w jednym wywołaniu (zwraca id)
CREATE OR REPLACE FUNCTION fn_add_quiz_attempt(
    p_user_id int,
    p_topic_id int,
    p_score int,
    p_total int,
    p_xp int
) RETURNS int
LANGUAGE plpgsql
AS $$
DECLARE
    v_id int;
BEGIN
    INSERT INTO quiz_attempts(user_id, topic_id, score, total, xp)
    VALUES (p_user_id, p_topic_id, p_score, p_total, p_xp)
    RETURNING id INTO v_id;

    RETURN v_id;
END;
$$;

-- TRIGGER: pilnuj progress 0..100 i updated_at
CREATE OR REPLACE FUNCTION trg_user_topic_progress_guard()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    NEW.updated_at := NOW();
    IF NEW.progress < 0 THEN NEW.progress := 0; END IF;
    IF NEW.progress > 100 THEN NEW.progress := 100; END IF;
    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS t_user_topic_progress_guard ON user_topic_progress;
CREATE TRIGGER t_user_topic_progress_guard
BEFORE INSERT OR UPDATE ON user_topic_progress
FOR EACH ROW
EXECUTE FUNCTION trg_user_topic_progress_guard();
