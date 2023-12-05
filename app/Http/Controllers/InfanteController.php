<?php

namespace App\Http\Controllers;

use App\Models\Infante;
use App\Models\Area;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Aws\S3\S3Client;
use Aws\Rekognition\RekognitionClient;
class InfanteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $infantes = Infante::paginate(5);
        $areas = Area::all();
        return view('infantes.index', compact('infantes', 'areas')); 
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $usuario=User::all();
        return view('infantes.crear',compact('usuario'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'nombre' => 'required|string|max:50',
            'apellidoPaterno' => 'required|string|max:50',
            'apellidoMaterno' => 'required|string|max:50',
            'edad' => 'required',
            'sexo' => 'required',
            'fechaNacimiento' => 'required|date',
            'sala' => 'required',
            'nombreMadre' => 'required',
            'nombrePadre' => 'required',
            'telefonoEmergencia' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'foto' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = new User([
            'name' => $request->input('nombre'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ]);
        $user->save();

        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $path = 'fotos/' . $user->id . '/' . $foto->getClientOriginalName();

            // Configuración de Amazon S3
            $s3 = new S3Client([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION'),
                'credentials' => [
                    'key' => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
            ]);

            // Subir la foto a Amazon S3
            $s3->putObject([
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $path,
                'Body' => file_get_contents($foto),
                'ACL' => 'public-read', // Esto hace que el objeto sea público
            ]);

            $fotoUrl = env('AWS_URL') . $path;

        $infante = new Infante([
            'nombre' => $request->input('nombre'),
            'apellidoPaterno' => $request->input('apellidoPaterno'),
            'apellidoMaterno' => $request->input('apellidoMaterno'),
            'edad' => $request->input('edad'),
            'sexo' => $request->input('sexo'),
            'fechaNacimiento' => $request->input('fechaNacimiento'),
            'nombreMadre' => $request->input('nombreMadre'),
            'nombrePadre' => $request->input('nombrePadre'),
            'telefonoEmergencia' => $request->input('telefonoEmergencia'),
            'sala' => $request->input('sala'),
            'foto' =>  $fotoUrl,
            'userId' => $user->id,
        ]);
        $infante->save();

        // Sube la imagen a Amazon S3

            // Indexar la cara en Rekognition con datos adicionales
            $rekognition = new RekognitionClient([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION'),
                'credentials' => [
                    'key' => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
            ]);

            $collectionId = 'fotos';
            $collections = $rekognition->listCollections();
            if (!in_array($collectionId, $collections['CollectionIds'])) {
            $rekognition->createCollection(['CollectionId' => $collectionId]);
            }


            $rekognition->indexFaces([
                'CollectionId' => 'fotos',
                'Image' => [
                    'S3Object' => [
                        'Bucket' => env('AWS_BUCKET'),
                        'Name' => $path,
                    ],
                ],
                'ExternalImageId' => strval($infante->id),
                'DetectionAttributes' => ['ALL'],
                'ExternalCustomAttributes' => [
                    'nombre' => (string) $infante->nombre,
                    'edad' => (string) $infante->edad,
                    'sala' => (string) $infante->sala,
                ],
            ]);
        }

        $user->assignRole('Padre');

        return redirect()->route('infantes.index');
    }


    /**
     * Show the form for showing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $infante = Infante::find($id);

        return view('infantes.ver',compact('infante'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $infante = Infante::find($id);

        return view('infantes.editar',compact('infante'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'nombre' => 'required|string|max:50',
            'apellidoPaterno' => 'required|string|max:50',
            'apellidoMaterno' => 'required|string|max:50',
            'edad' => 'required',
            'sexo' => 'required',
            'fechaNacimiento' => 'required|date',
            'sala' => 'required',
            'nombreMadre' => 'required',
            'nombrePadre' => 'required',
            'telefonoEmergencia' => 'required'
        ]);

        $input = $request->all();
        $infante = Infante::find($id);
        $infante->update($input);

        return redirect()->route('infantes.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Infante::destroy($id);
        return redirect()->route('infantes.index');
    }

    public function info_infante(Request $request)
    { 
        try {
            $this->validate($request, [
                'foto' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
    
            $foto = $request->file('foto');
            $rekognition = new RekognitionClient([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION'),
                'credentials' => [
                    'key' => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
            ]);
    
            $result = $rekognition->searchFacesByImage([
                'CollectionId' => 'fotos', 
                'Image' => [
                    'Bytes' => file_get_contents($foto),
                ],
            ]);
    
            $matchedFace = $result->get('FaceMatches')[0] ?? null;
    
            if ($matchedFace) {
                $infanteId = $matchedFace['Face']['ExternalImageId'];
                $infante = Infante::find($infanteId);
    
                return response()->json([
                    'infante_id' => $infanteId,
                    'nombre' => $infante->nombre,
                    'edad' => $infante->edad,
                    'sala' => $infante->sala,
                    'message' => 'Rostro coincidente encontrado.',
                ]);
            } else {
                // No se encontraron coincidencias en la colección
                return response()->json(['message' => 'Rostro no encontrado en la colección.']);
            }
        } catch (\Exception $e) {
            // Log the specific error details
            \Illuminate\Support\Facades\Log::error('Error en info_infante: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e);
            return response()->json(['message' => 'Error en el servidor.'], 500);
        }
    }

    public function getInfanteInfo($infanteId)
    {
        try {
            // Aquí obtén la información del infante desde tu base de datos
            $infante = Infante::find($infanteId);

            if (!$infante) {
                return response()->json(['error' => 'Infante no encontrado'], 404);
            }

            // Devuelve la información del infante
            return response()->json([
                'infante_id' => $infante->id,
                'nombre' => $infante->nombre,
                'edad' => $infante->edad,
                'sala' => $infante->sala,
                'message' => 'Información del infante obtenida con éxito',
            ]);
        } catch (\Exception $e) {
            // Maneja cualquier excepción que pueda ocurrir
            return response()->json(['error' => 'Error en el servidor'], 500);
        }
    }
}
    
