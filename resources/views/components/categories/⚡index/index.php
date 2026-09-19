<?php

use Livewire\Component;
use App\Models\Category;

new class extends Component
{
    public $nombre;
    public $categoria_id_editando = null;

    public function guardar()
    {
        $this->validate(['nombre' => 'required|string|max:255']);

        if ($this->categoria_id_editando) {
            Category::find($this->categoria_id_editando)->update(['nombre' => $this->nombre]);
            session()->flash('ok', 'Categoría actualizada.');
        } else {
            Category::create(['nombre' => $this->nombre]);
            session()->flash('ok', 'Categoría creada.');
        }

        $this->reset(['nombre', 'categoria_id_editando']);
    }

    public function editar($categoriaId)
    {
        $categoria = Category::find($categoriaId);
        $this->categoria_id_editando = $categoria->id;
        $this->nombre = $categoria->nombre;
    }

    public function cancelarEdicion()
    {
        $this->reset(['nombre', 'categoria_id_editando']);
    }

    public function cambiarEstado($categoriaId)
    {
        $categoria = Category::find($categoriaId);
        $categoria->update(['estado' => $categoria->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO']);
    }

    public function render()
    {
        return $this->view([
            'categorias' => Category::latest()->get(),
        ]);
    }
};
