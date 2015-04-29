<?php

namespace BiSight\Portal\Repository;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use BiSight\Portal\Model\User;
use PDO;

class PdoUserRepository implements UserProviderInterface
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function loadUserByUsername($username)
    {
        $sql = "SELECT passwordhash,roles FROM user WHERE username=:username LIMIT 1";
        $statement = $this->pdo->prepare($sql);
        $statement->execute(array('username' => $username));
        $row = $statement->fetch();

        if (!$row) {
            throw new UsernameNotFoundException(sprintf('User %s is not found.', $username));
        }
        $roles = explode(',', $row['roles']);

        return new User($username, $row['passwordhash']);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}
