<?php


use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $data = [
            [
                'username' => 'root',
//                'password' => Hash::make('secret'),
                'password' => password_hash('secret', PASSWORD_BCRYPT, ['cost' => 12]),
                'email' => 'admin@email.com',
                'name' => 'Root Admin',
                'created' => date('Y-m-d H:i:s'),
                'updated' => date('Y-m-d H:i:s'),
            ],
        ];

        $users = $this->table('users');
        $users->insert($data)->saveData();
    }
}