<div class="modal fade" id="modal-eliminar-{{$cliente->id}}" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalLabel">
    <div class="modal-dialog">
        <div class="modal-content bg-danger">
            <form action="{{route('clientes.destroy', $cliente->id)}}" method="post">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h4 class="modal-title">Eliminar registro</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    ¿Usted desea eliminar el registro {{$cliente->nombre}} ?
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-outline-light">Eliminar</button>
                </div>
            </form>
        </div>
            </div>
    </div>