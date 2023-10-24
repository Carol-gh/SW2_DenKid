<?php

namespace Database\Seeders;

use App\Models\Infante;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
class InfanteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
     
        $user = new User();
        $user->name = 'Emma Justine';
        $user->email = 'emma@gmail.com';       
        $user->password = bcrypt('12345');
        $user->save();
        $user->assignRole('Padre');

        $infante = new Infante();
        $infante->nombre = 'Emma Justine';
        $infante->apellidoPaterno = 'Acevedo';
        $infante->apellidoMaterno = 'Martinez';
        $infante->edad = 1;
        $infante->sexo = 'F';
        $infante->fechaNacimiento = '2019-06-30';
        $infante->nombreMadre = 'Rosalia Martinez';
        $infante->nombrePadre = 'Juan Carlos Acevedo';
        $infante->telefonoEmergencia = '65248754';
        $infante->sala = '1';
        $infante->userId = $user->id;
        $infante->save();
        //////////////////////////////////////////////////

        $user1 = User::create([
            'name' => 'Daniel Vidal',
            'email' => 'daniel123@gmail.com',
            'password' => bcrypt('12345'),
        ]);

        $user1->assignRole('Padre');
        
        $infante = new Infante();
        $infante->nombre = 'Daniel';
        $infante->apellidoPaterno = 'Vidal';
        $infante->apellidoMaterno = 'Martinez';
        $infante->edad = 2;
        $infante->sexo = 'F';
        $infante->fechaNacimiento = '2021-06-10';
        $infante->nombreMadre = 'Maria Martinez';
        $infante->nombrePadre = 'Marco Vidal';
        $infante->telefonoEmergencia = '65248754';
        $infante->sala = '1';
        $infante->userId = $user->id;
        $infante->save();
////////////////////////////////////////////////////////////////
        $user = new User();
        $user->name = 'Vannesa Colque';
        $user->email = 'vannesa@gmail.com';       
        $user->password = bcrypt('12345');
        $user->save();
        $user->assignRole('Padre');

        $infante = new Infante();
        $infante->nombre = 'Vannesa';
        $infante->apellidoPaterno = 'Martinez';
        $infante->apellidoMaterno = 'Colque';
        $infante->edad = 1;
        $infante->sexo = 'F';
        $infante->fechaNacimiento = '2019-03-30';
        $infante->nombreMadre = 'Rosalia Colque';
        $infante->nombrePadre = 'Juan Carlos Martinez';
        $infante->telefonoEmergencia = '65248754';
        $infante->sala = '1';
        $infante->userId = $user->id;
        $infante->save();
        
 
    }
}
