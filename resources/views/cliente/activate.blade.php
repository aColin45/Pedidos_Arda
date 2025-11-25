<div class="modal fade" id="modal-toggle-{{$cliente->id}}" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalLabel">
    <div class="modal-dialog">
        <div class="modal-content {{$cliente->activo ? 'bg-warning' : 'bg-success'}}">
            <form action="{{route('clientes.toggle', $cliente->id)}}" method="post">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h4 class="modal-title">{{$cliente->activo ? 'Desactivar ' : 'Activar '}} registro</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    ¿Usted desea  {{$cliente->activo ? 'desactivar ' : 'activar '}} el registro {{$cliente->nombre}} ?
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-outline-light">{{$cliente->activo ? 'Desactivar ' : 'Activar '}}</button>
                </div>
            </form>
        </div>
            </div>
    </div>