<?php

namespace App\Models;

use App\Models\Category as Categories;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class User extends Model
{
    public $username, $password, $name, $email;
    public $timestamps  = true;
    protected $fillable = ['username', 'password', 'name', 'email'];
    protected $table    = 'users';
    protected $rules = [
        'username' => 'required|min:3',
        'name' => 'required|min:3',
        'password' => 'required|min:6',
        'email' => 'required|email|unique:users|email_address',
    ];

    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public function stockRequests()
    {
        return $this->hasMany(UserStockRequest::class);
    }

    public function getUserByEmail(string $email)
    {
        return User::where('email', $email)->first();
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

    public function create(array $data)
    {
        $this->id = '';
        $this->email = $data['email'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->username = $data['username'] ?? '';
        $this->setPassword($data['password'] ?? '');
        return $this->store();
    }

    public function store()
    {
        // TODO: Add illuminate/validation dependency and validate the data
//        $this->validate();

        return User::updateOrCreate(['id' => $this->id], [
            'name' => $this->name,
            'username' => $this->username,
            'password' => $this->password,
            'email' => $this->email,
        ]);
    }
}