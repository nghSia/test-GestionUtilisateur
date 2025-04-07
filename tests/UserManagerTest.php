<?php
namespace Ngh\tests;

use PHPUnit\Framework\TestCase;
use Ngh\TpGestionUtilisateur\UserManager;
use PDO;
use InvalidArgumentException;
use Exception;

class UserManagerTest extends TestCase
{
    protected $pdo;
    protected $userManager;

    protected function setUp(): void
    {
        $dsn = "mysql:host=localhost;dbname=user_management;charset=utf8";
        $username = "root";
        $password = "";
        $this->pdo = new PDO($dsn, $username, $password);

        $this->userManager = new UserManager();
    }

    public function testAddUser(): void
    {
        $name = 'test_user';
        $email = 'test_user@example.com';

        $this->userManager->addUser($name, $email);
        $users = $this->userManager->getUsers();
        $this->assertNotEmpty($users);
        $this->assertEquals($name, $users[0]['name'], 'Le nom de l\'utilisateur ajouté est incorrect.');
        $this->assertEquals($email, $users[0]['email'], 'L\'email de l\'utilisateur ajouté est incorrect.');
    }

    public function testAddUserEmailException(): void
    {
        $name = 'test_invalid_user';
        $email = 'test_invalid_email';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Email invalide.");
        $this->userManager->addUser($name, $email);
    }

    public function testUpdateUser(): void 
    {
        $user = $this->userManager->getUsers()[0];
        $updatedName = 'updated_user';
        $updatedEmail = 'test_user@gmail.com';
        $this->userManager->updateUser($user['id'], $updatedName, $updatedEmail);
        $updatedUser = $this->userManager->getUser($user['id']);
        $this->assertEquals($updatedName, $updatedUser['name'], 'Le nom de l\'utilisateur mis à jour est incorrect.');
        $this->assertEquals($updatedEmail, $updatedUser['email'], 'L\'email de l\'utilisateur mis à jour est incorrect.');
    }

    public function testRemoveUser(): void
    {
        $users = $this->userManager->getUsers();
        $initialUserCount = count($users);

        $newUserName = 'toto';
        $newUserEmail = 'toto@gmail.com';
        $this->userManager->addUser($newUserName, $newUserEmail);
        
        $users = $this->userManager->getUsers();
        $this->assertEquals($newUserName, $users[count($users) - 1]['name']);
        $this->assertEquals($newUserEmail, $users[count($users) - 1]['email']);

        $newUserCount = count($users);
        $this->assertEquals($initialUserCount + 1, $newUserCount, 'Le nombre d\'utilisateurs après l\'ajout est incorrect.');

        $userToDelete = $this->userManager->getUsers()[0];
        $this->userManager->removeUser($userToDelete['id']);

        $usersAfterDelete = $this->userManager->getUsers();

        $finalUserCount = count($usersAfterDelete);
        $this->assertEquals($newUserCount - 1, $finalUserCount, 'Le nombre d\'utilisateurs après la suppression est incorrect.');

        $userExists = false;
        foreach ($usersAfterDelete as $user) {
            if ($user['id'] === $userToDelete['id']) {
                $userExists = true;
                break;
            }
        }
        $this->assertFalse($userExists, 'L\'utilisateur supprimé est toujours présent.');
    }

    public function testGetUsers(): void
    {
        $users = $this->userManager->getUsers();
        $this->assertNotEmpty($users, 'La liste des utilisateurs est vide.');
        $actualUserCount = count($users);
        $newUserName = 'tata';
        $newUserEmail = 'tata@gmail.com';
        $this->userManager->addUser($newUserName, $newUserEmail);
        $usersAfterAdd = $this->userManager->getUsers();
        $this->assertEquals($newUserName, $usersAfterAdd[count($usersAfterAdd) - 1]['name'], 'Le nom de l\'utilisateur ajouté est incorrect.');
        $this->assertEquals($newUserEmail, $usersAfterAdd[count($usersAfterAdd) - 1]['email'], 'L\'email de l\'utilisateur ajouté est incorrect.');
        $this->assertEquals($actualUserCount + 1, count($usersAfterAdd), 'Le nombre d\'utilisateurs après l\'ajout est incorrect.');
    }

    public function testInvalidUpdateThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Utilisateur introuvable.");
        $this->userManager->updateUser(9999, 'test_update_user', 'test_update_email@example.com');
    }

    public function testInvalidDeleteThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Utilisateur introuvable.");
        $this->userManager->removeUser(9999);
    }
}
