<?php

namespace App\Livewire\Auth;

use App\Models\AcademicSession;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class LdapLogin extends Component
{
    public $username = '';

    public $password = '';

    public $remember = false;

    public $error = '';

    public function render()
    {
        return view('livewire.auth.ldap-login');
    }

    public function login()
    {
        $this->validate([
            'username' => 'required|regex:/^[a-zA-Z]+[0-9]+[a-zA-Z]+$/',  // looks like a staff guid
            'password' => 'required',
        ]);

        $this->error = '';

        // Try LDAP authentication (if enabled)
        $ldapUser = null;
        if (config('ldap.enabled')) {
            if (! \Ldap::authenticate($this->username, $this->password)) {
                $this->error = 'Invalid username or password';
                $this->password = '';

                return;
            }

            $ldapUser = \Ldap::findUser($this->username);
            if (! $ldapUser) {
                $this->error = 'Invalid username or password';
                $this->password = '';

                return;
            }
        }

        // Create or update the user in our database
        $currentSession = AcademicSession::getDefault();
        if (! $currentSession) {
            $this->error = 'No default academic session found';
            $this->password = '';

            return;
        }

        $localUser = User::forAcademicSession($currentSession)->where('username', $this->username)->first();
        if (! $localUser && ! $ldapUser) {
            $this->error = 'Invalid username or password';
            $this->password = '';
            info('Not using LDAP and no local user found: '.$this->username);

            return;
        }

        if ($ldapUser) {
            // if we have an LDAP user, update or create the local user with the current LDAP details (name changes etc)
            $localUser = User::updateOrCreate(
                ['username' => $this->username, 'academic_session_id' => $currentSession->id],
                [
                    'forenames' => $ldapUser->forenames,
                    'surname' => $ldapUser->surname,
                    'email' => $ldapUser->email,
                    'is_staff' => true,
                    'academic_session_id' => $currentSession->id,
                    'password' => bcrypt(Str::random(32)),
                ]
            );
        }

        if ($localUser && ! $ldapUser) {
            if (! Auth::attempt([
                'username' => $this->username,
                'password' => $this->password,
            ])) {
                $this->error = 'Invalid username or password';
                $this->password = '';

                return;
            }
        }
        Auth::login($localUser, $this->remember);

        return redirect()->route('home');
    }
}
