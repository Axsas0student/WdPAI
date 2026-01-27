<?php

require_once 'Repository.php';

class UserRepository extends Repository
{
    public function getUsers(): ?array
    {
        $query = $this->database->connect()->prepare('
            SELECT id, firstname, lastname, email, bio, enabled, is_admin
            FROM users
        ');
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserByEmail(string $email): ?array
    {
        $query = $this->database->connect()->prepare('
            SELECT id, firstname, email, password, is_admin
            FROM users
            WHERE email = :email
            LIMIT 1
        ');
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();

        $user = $query->fetch(PDO::FETCH_ASSOC);
        return $user === false ? null : $user;
    }


    public function emailExists(string $email): bool
    {
        $query = $this->database->connect()->prepare('
            SELECT 1 FROM users WHERE email = :email LIMIT 1
        ');
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        return (bool)$query->fetchColumn();
    }

    public function createUser(
        string $email,
        string $hashedPassword,
        string $firstName,
        string $lastName,
        string $bio = ''
    ): void {
        $query = $this->database->connect()->prepare('
            INSERT INTO users (firstname, lastname, email, password, bio, is_admin)
            VALUES (?, ?, ?, ?, ?, FALSE);
        ');

        $query->execute([$firstName, $lastName, $email, $hashedPassword, $bio]);
    }
}