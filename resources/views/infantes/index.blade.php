@extends('dashboard.app')

@section('title', 'Infantes')

@section('content')
<section class="section">
 <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="section-header">
        <h3 class="page__heading">Infantes</h3>
    </div>
    <div class="section-body">
    @csrf
    
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        @can('crear-infante')
                        <a class="btn btn-primary" href="{{ route('infantes.create') }}">Nuevo</a>
                        @endcan
                        @if(Auth::user()->hasAnyRole(['Administrador', 'Superadmin', 'Secretari@']))
                        <table class="table table-striped mt-2">
                            <thead style="background-color: #eb3c76ee">
                                <tr>
                                    <th style="display: none">ID</th>
                                    <th style="color: #fff">Nombre</th>
                                    <th style="color: #fff">Apellido Paterno</th>
                                    <th style="color: #fff">Apellido Materno</th>
                                    <th style="color: #fff">Edad</th>
                                    <th style="color: #fff">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($infantes as $infante)
                                <tr>
                                    <td style="display: none">{{ $infante->id }}</td>
                                    <td>{{ $infante->nombre }}</td>
                                    <td>{{ $infante->apellidoPaterno }}</td>
                                    <td>{{ $infante->apellidoMaterno }}</td>
                                    <td>{{ $infante->edad }}</td>
                                    <td>
                                        @can('ver-infante')
                                        <a class="btn btn-secondary"
                                            href="{{ route('infantes.show', $infante->id) }}">Ver</a>
                                        @endcan
                                        @can('editar-infante')
                                        <a class="btn btn-success"
                                            href="{{ route('infantes.edit', $infante->id) }}">Editar</a>
                                        @endcan
                                        @can('borrar-infante')
                                        {!! Form::open([
                                        'method' => 'DELETE',
                                        'route' => ['infantes.destroy', $infante->id],
                                        'style' => 'display:inline',
                                        ]) !!}
                                        {!! Form::submit('Borrar', ['class' => 'btn btn-danger']) !!}
                                        {!! Form::close() !!}
                                        @endcan
                                        @can('evaluar-infante')
                                        <a class="btn btn-secondary" href="{{ route('evaluar', $infante->id) }}">Evaluar</a>
                                        @endcan
                                        @can('seguimiento-infante')
                                        <a class="btn btn-secondary" href="{{ route('seguimiento.index') }}">Seguimiento</a>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="pagination justify-content-end">
                            {{ $infantes->links() }}
                        </div>
                        @else
                        <div class="container">
                            <label for="" id="csrf" hidden="true">{{ csrf_token() }}</label>
                            <div class="row">
                                <div class="col-lg-8 mx-auto">
                                    <div class="ratio ratio-16x9">
                                        <small class="fs-4" id="label_video_loading">Cargando video...</small>
                                        <video class="embed-responsive-item" id="video" autoplay muted></video>
                                    </div>
                                    <div class="video-info text-center p-2">
                                        <div class="video-info-container">
                                            <div class="list-group">
                                                
                                                <small class="fs-4" id="label-waiting" style="display:none;">Analizando...</small>

                                                <div class="" id="alert-analized" role="alert" style="display:none;">
                                                    <strong class="fs-4" id="label-result">Aceptado</strong>
                                                </div>
                                                
                                                <div class="card" id="container-person" style="display:none;">
                                                    <strong class="fs-4">Nombre: </strong>
                                                    <small class="fs-4" id="name-result">Juan Perez Chumacero</small>
                                                </div>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary m-2" id="btn-start">Comenzar</button>
                                        <strong class="fs-6" id="label-redirect"></strong>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <!-- Carga jQuery primero -->
                                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                                <script type="module" src="{{ asset('js/rekognition.js') }}"></script>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
