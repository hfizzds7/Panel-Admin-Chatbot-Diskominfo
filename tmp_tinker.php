$user = App\Models\User::first(); $user->password = bcrypt("123456"); $user->save(); echo "Done\n";
