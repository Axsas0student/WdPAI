<?php

require_once 'Repository.php';

class UserRepository extends Repository{

	public function getUsers(): ?array
	{
		$query = $this->database->connnect()->prepare('
			SELECT * FROM users
		');
		$query->execute();

		$users = $query->fetchAll(PDO::FETCH_ASSOC);

		return $users;
	}

	public function getUserByEmail(string $email): ?array
	{
		$query = $this->database->connnect()->prepare('
			SELECT * FROM users WHERE email = :email
		');
		$query->bindParam(':email', $email);
		$query->execute();

		$users = $query->fetch(PDO::FETCH_ASSOC);
		//TODO close db connection
		return $users;
	}

	public function createUser(
		string $email,
		string $hashedPassword,
		string $firstname,
		string $lastname,
		string $bio = ''
	): void {
		$query = $this->database->connect()->prepare('
			INSERT INTO users (firstname, lastname, email, password, bio)
			VALUES (?, ?, ?, ?, ?);
		');
	}
}