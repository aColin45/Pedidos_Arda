@extends('web.app')
@section('contenido')

<section class="py-5">
    <div class="container px-4 px-lg-5 my-5">
        <h2 class="fw-bold mb-4">
            @if(Auth::user()->hasRole('admin'))
                Selección de Cliente (ADMIN)
            @else
                Selección de Cliente
            @endif
        </h2>
        
        <p class="lead text-muted mb-5">
            Seleccione el cliente para el cual desea generar un nuevo pedido. 
            El carrito actual se vaciará y se asociará al cliente seleccionado.
        </p>

        {{-- Mensajes de Sesión (Éxito/Advertencia) --}}
        @if(session('mensaje'))
        <div class="alert alert-info alert-dismissible fade show mt-2" role="alert">
            {{ session('mensaje') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
        @endif
        
        {{-- Listado de Clientes --}}
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            
            @forelse ($clientes as $cliente)
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title fw-bold text-primary">{{ $cliente->nombre }}</h5>
                            <p class="card-text mb-1">
                                <i class="bi bi-person-circle me-1"></i> Contacto: {{ $cliente->contacto ?? 'N/A' }}
                            </p>
                            <p class="card-text mb-1">
                                <i class="bi bi-phone me-1"></i> Teléfono: {{ $cliente->telefono ?? 'N/A' }}
                            </p>
                            <p class="card-text mb-3">
                                <i class="bi bi-geo-alt-fill me-1"></i> Dirección: {{ Str::limit($cliente->direccion, 35) }}
                            </p>
                            
                            <a href="{{ route('pedido.start', $cliente->id) }}" class="btn btn-success w-100">
                                <i class="bi bi-cart-plus me-2"></i> Iniciar Pedido
                            </a>
                            
                            @if(Auth::user()->hasRole('admin'))
                            <small class="d-block text-center mt-2 text-muted">Agente: {{ $cliente->agente->name ?? 'Sin asignar' }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        No hay clientes activos asignados a tu cuenta.
                    </div>
                </div>
            @endforelse

        </div>

    </div>
</section>
@endsection