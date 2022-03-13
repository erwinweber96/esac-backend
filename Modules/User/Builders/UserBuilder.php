<?php


namespace Modules\User\Builders;

use Illuminate\Support\Facades\Hash;
use App\Model\Builder;

/**
 * Class UserBuilder
 * @package Modules\User\Builders
 */
class UserBuilder implements Builder
{
    /** @var array */
    private $userData;

    /**
     * @return Builder
     */
    public function prepare(): Builder
    {
        $this->userData = [];
        return $this;
    }

    /**
     * @param string $nickname
     * @return UserBuilder
     */
    public function setNickname(string $nickname): UserBuilder
    {
        $this->userData['nickname'] = $nickname;
        return $this;
    }

    /**
     * @param string $email
     * @return UserBuilder
     */
    public function setEmail(string $email): UserBuilder
    {
        $this->userData['email'] = $email;
        return $this;
    }

    /**
     * @param string $password
     * @return UserBuilder
     */
    public function setPassword(string $password): UserBuilder
    {
        $this->userData['password'] = Hash::make($password);
        return $this;
    }

    /**
     * @param string $firstName
     * @return UserBuilder
     */
    public function setFirstName(string $firstName): UserBuilder
    {
        $this->userData['first_name'] = $firstName;
        return $this;
    }

    /**
     * @param string $lastName
     * @return UserBuilder
     */
    public function setLastName(string $lastName): UserBuilder
    {
        $this->userData['last_name'] = $lastName;
        return $this;
    }

    /**
     * @param string $nat
     * @return UserBuilder
     */
    public function setNat(string $nat): UserBuilder
    {
        $this->userData['nat'] = $nat;
        return $this;
    }

    /**
     * @return array
     */
    public function build(): array
    {
        return $this->userData;
    }
}
