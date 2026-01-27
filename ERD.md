# ERD (HistorIQ)

```mermaid
erDiagram
  USERS ||--o{ QUIZ_ATTEMPTS : takes
  TOPICS ||--o{ QUESTIONS : has
  QUESTIONS ||--o{ ANSWERS : has
  USERS ||--o{ USER_TOPIC_PROGRESS : tracks
  TOPICS ||--o{ USER_TOPIC_PROGRESS : tracks
  TOPICS ||--o{ QUIZ_ATTEMPTS : for

  USERS {
    int id PK
    varchar firstname
    varchar lastname
    varchar email UK
    varchar password
    text bio
    bool enabled
    bool is_admin
  }

  TOPICS {
    int id PK
    varchar name
    varchar icon
    bool is_locked
    int progress
    int sort_order
  }

  QUESTIONS {
    int id PK
    int topic_id FK
    text content
  }

  ANSWERS {
    int id PK
    int question_id FK
    text content
    bool is_correct
  }

  QUIZ_ATTEMPTS {
    int id PK
    int user_id FK
    int topic_id FK
    int score
    int total
    int xp
    timestamptz finished_at
  }

  USER_TOPIC_PROGRESS {
    int user_id PK,FK
    int topic_id PK,FK
    int progress
    timestamptz updated_at
  }
