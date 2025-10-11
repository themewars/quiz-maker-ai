<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Actions\Subscription\CreateSubscription;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    protected $redirectTo = '/user';

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        // Assign default role
        $user->assignRole(User::USER_ROLE);

        // Create default subscription
        $this->createDefaultSubscription($user);

        // Send email verification notification (disabled for now due to mail server issues)
        // $user->sendEmailVerificationNotification();

        auth()->login($user);

        return redirect($this->redirectTo)->with('status', 'Registration successful! Welcome to ' . getAppName() . '.');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => true,
            'email_verified_at' => now(), // Auto-verify email for now
        ]);
    }

    protected function createDefaultSubscription(User $user)
    {
        $plan = Plan::where('assign_default', true)->first();
        if ($plan) {
            $data['plan'] = $plan->load('currency')->toArray();
            $data['user_id'] = $user->id;
            $data['payment_type'] = Subscription::TYPE_FREE;
            if ($plan->trial_days != null && $plan->trial_days > 0) {
                $data['trial_days'] = $plan->trial_days;
            }
            CreateSubscription::run($data);
        }
    }
}
