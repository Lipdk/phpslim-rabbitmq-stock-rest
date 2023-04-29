<?php

namespace App\Models;

use App\Utilities\Config;
use Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public $timestamps  = true;
    protected $fillable = ['username', 'password', 'name', 'email'];
    protected $table    = 'users';

    public function passwordHash(string $password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function stockRequests()
    {
        return $this->hasMany(UserStockRequest::class);
    }

    public function getUserByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    public function getUserByUsername(string $username)
    {
        return User::where('username', $username)->first();
    }

    public function auth(string $email, string $password)
    {
        $user = $this->getUserByEmail($email);

        if (is_null($user)) {
            return ['error' => 'User not found'];
        }

        if (!password_verify($password, $user->password)) {
            return ['error' => 'Invalid password'];
        }

        return $user;
    }

    /**
     * @param array $data
     * @return User
     * @throws \Exception
     */
    public function store($data)
    {
        $payload = [
            'name' => $data['name'] ?? '',
            'username' => $data['username'] ?? '',
            'password' => $this->passwordHash($data['password'] ?? ''),
            'email' => $data['email'] ?? '',
        ];

        $this->validate($payload);

        $user = $this->getUserByEmail($payload['email']);

        if ($user instanceof User) {
            throw new \Exception('User already exists');
        }

        $user = $this->getUserByUsername($payload['username']);

        if ($user instanceof User) {
            throw new \Exception('User already exists');
        }

        return User::create($payload);
    }

    /**
     * @param array $payload
     * @return void
     * @throws \Exception
     */
    public function validate(array $payload)
    {
        if (empty($payload['email']) || empty($payload['name'] || empty($payload['username']) || empty($payload['password']))) {
            throw new \Exception('All fields are required');
        }

        if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid e-mail address');
        }
    }

    /**
     * @param string $email
     * @return string
     */
    public function generateJwtToken(string $email)
    {
        $expire = (new \DateTime('now'))->modify('+1 hour')->format('Y-m-d H:i:s');
        return JWT::encode([
            'expired_at' => $expire,
            'email' => $email,
        ], Config::getJwtKeyMaterial());
    }
}