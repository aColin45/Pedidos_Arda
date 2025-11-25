@extends('web.app')
@section('contenido')

<section class="py-5">
    <div class="container px-4 px-lg-5 my-5">
        <div class="row gx-4 gx-lg-5 align-items-center">
            <div class="col-md-6">
                <img class="card-img-top mb-5 mb-md-0" src="{{asset('uploads/productos/'. $producto->imagen) }}"
                    alt="{{$producto->nombre}}" />
            </div>

            <div class="col-md-6">
                <div class="small mb-1">Código: {{$producto->codigo}}</div>
                <h1 class="display-5 fw-bolder">{{$producto->nombre}}</h1>
                <div class="fs-5 mb-5">
                    <span>${{$producto->precio}}</span>
                </div>

                {{-- ... (Descripción del producto) ... --}}
                <p class="lead">{!! nl2br(e($producto->descripcion)) !!}</p>

                {{-- =================================== --}}
                {{-- ||   TABLA DE ESPECIFICACIONES     || --}}
                {{-- =================================== --}}
                @if($producto->especificaciones)
                <h4 class="mt-5 mb-3">Especificaciones Técnicas</h4>
                <table class="table table-striped table-bordered">
                    <tbody>
                        @php
                        // Dividir el texto por saltos de línea
                        $lineas = explode("\n", $producto->especificaciones);
                        @endphp

                        @foreach($lineas as $linea)
                        @php
                        // Dividir cada línea por el símbolo '|'
                        // Usamos list() para asignar las partes a variables
                        // array_pad asegura que siempre tengamos 2 elementos, incluso si falta el '|'
                        list($caracteristica, $valor) = array_pad(explode('|', $linea, 2), 2, null);
                        @endphp

                        @if(trim($caracteristica)) {{-- Asegurarse de que no esté vacía --}}
                        <tr>
                            <th style="width: 40%;">{{ trim($caracteristica) }}</th>
                            <td>{{ trim($valor) }}</td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
                @endif
                {{-- =================================== --}}

                @if(session('mensaje'))
                <div class="alert alert-success alert-dismissible fade show **mt-3 mb-3**" role="alert">
                    {{ session('mensaje') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
                @endif
                <form action="{{ route('carrito.agregar') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <p class="text-muted small mb-1">
                            Unidad de Empaque Mínima (Inner):
                            <strong>{{ $producto->inner ?? 1 }}</strong> unidades.
                            (Pedidos en múltiplos de este valor)
                        </p>
                    </div>

                    <div class="d-flex">
                        <input type="hidden" name="producto_id" value="{{ $producto->id }}">

                        <input class="form-control text-center me-3" id="inputQuantity" name="cantidad" type="number"
                            min="{{ $producto->inner ?? 1 }}" step="{{ $producto->inner ?? 1 }}"
                            value="{{ $producto->inner ?? 1 }}" style="max-width: 6rem" required />

                        <button class="btn btn-primary flex-shrink-0" type="submit">
                            <i class="bi-cart-fill me-1"></i>
                            Agregar al carrito
                        </button>

                        <a class="btn btn-secondary ms-2" href="{{ route('web.index') }}">Regresar</a>
                    </div>
                </form>

                @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                @if (session('error'))
                <div class="alert alert-danger mt-3 alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection