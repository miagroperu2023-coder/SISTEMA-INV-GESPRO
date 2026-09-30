<?php

use Livewire\Component;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

new class extends Component
{
    public $name;
    public $email;
    public $password = '';
    public $password_confirmation = '';

    public function mount()
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function guardar()
    {
        $user = auth()->user();

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|min:6|confirmed',
        ]);

        $datos = ['name' => $this->name, 'email' => $this->email];

        if (!empty($this->password)) {
            $datos['password'] = Hash::make($this->password);
        }

        $user->update($datos);
        $this->reset(['password', 'password_confirmation']);

        session()->flash('ok', 'Tus datos se actualizaron correctamente.');
    }

    public function render()
    {
        return $this->view();
    }
};
