<?php

namespace Database\Seeders;

use App\Models\AcademicAdministration;
use App\Models\Coordinator;
use App\Models\Examiner;
use App\Models\HeadStudyProgram;
use App\Models\Room;
use App\Models\Student;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     
    public function run(): void
    {
        function generateNrp() {
            $min = 100000000;  // Angka terkecil dengan 9 digit
            $max = 999999999;  // Angka terbesar dengan 9 digit
        
            return random_int($min, $max);
        }
        
     
        function generatePhone($length = 12) {
            if ($length < 2) {
                throw new InvalidArgumentException('Length must be at least 2');
            }
        
            $firstDigit = '0'; // Angka pertama selalu 0
            $remainingLength = $length - 1;
        
            // Generate remaining digits
            $remainingDigits = '';
            for ($i = 0; $i < $remainingLength; $i++) {
                $remainingDigits .= random_int(0, 9);
            }
        
            return $firstDigit . $remainingDigits;
        }
        
        $user = new User();
        $user->username = "DedyAri@gmail.com";
        $user->password = Hash::make("123");
        $user->role = "academic-administrations";
        $user->save();

        $baa = new AcademicAdministration();
        $baa->user_id = $user->id;
        $baa->name = "Dedy Ari P., S.Kom";
        $baa->address = "Jl. Tidar Malang";
        $baa->phone =  generatePhone();
        $baa->save();
    }
}