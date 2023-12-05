import { RekognitionClient, SearchFacesByImageCommand } from 'https://cdn.skypack.dev/@aws-sdk/client-rekognition';


const rekognition = new RekognitionClient({
    region: 'us-east-1', // Reemplaza con tu región de AWS
    credentials: {
        accessKeyId: 'AKIA4UYHI5DYPERN2SOX',
        secretAccessKey: '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',
    },
});
const video = document.getElementById("video");
const labelWaiting = document.getElementById("label-waiting");
const labelVideoLoading = document.getElementById("label_video_loading");
const alertAnalized = document.getElementById("alert-analized");
const labelRedirect = document.getElementById("label-redirect");
const containerPerson = document.getElementById("container-person");
const btnStart = document.getElementById("btn-start");


async function startVideo() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
        video.srcObject = stream;
        await video.play();
        labelVideoLoading.style.display = "none";
    } catch (error) {
        console.error('Error al acceder a la cámara:', error)
    }
}
startVideo();

function captureFrame() {
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    return new Promise( (resolve, reject) => {
        canvas.toBlob( (blob) => {
            if(blob)
                resolve(blob);
            else
                reject(new Error("Error al crear el blob desde el canvas"));
        }, 'image/png');
    })
}  


function clearInfo() {
    labelWaiting.style.display = "none";
    alertAnalized.style.display = "none";
    containerPerson.style.display = "none";
}

function changeTextContainerPerson(name) {
    var nameResult = document.getElementById("name-result");
    nameResult.textContent = name;
    containerPerson.style.display = "block";
}


async function getInfanteInfoFromDatabase(infanteId) {
    try {
        const response = await fetch(`/api/infantes/${infanteId}`);
        const data = await response.json();
        
        if (response.ok) {
            return data;
        } else {
            throw new Error(data.message || 'Error al obtener información del infante desde la base de datos');
        }
    } catch (error) {
        console.error('Error en la solicitud al backend:', error);
        throw error;  // Puedes manejar el error según sea necesario
    }
}

function changeTextAlertAnalized(classname, label) {
    var labelResult = document.getElementById("label-result");
    labelResult.textContent = label;
    alertAnalized.className = classname;
    alertAnalized.style.display = "block";
}
async function detectFace(image) {
    try {
        const imgBlob = new Blob([image], { type: 'image/png' });

        // Usar FileReader para leer el blob como un array buffer
        const imgArrayBuffer = await new Promise((resolve) => {
            const reader = new FileReader();
            reader.onloadend = () => resolve(reader.result);
            reader.readAsArrayBuffer(imgBlob);
        });

        const result = await rekognition.send(new SearchFacesByImageCommand({
            CollectionId: 'fotos',
            Image: {
                Bytes: new Uint8Array(imgArrayBuffer),
            },
        }));

        if (result.FaceMatches && result.FaceMatches.length > 0) {
            const matchedFace = result.FaceMatches[0].Face;

            if (matchedFace) {
                const infanteId = matchedFace.ExternalImageId;
                updateUIWithInfanteInfo(infanteId);
            } else {
                updateUIWithNoMatch();
            }
        } else {
            updateUIWithNoMatch();
        }
    } catch (error) {
        console.error('Error durante la detección de rostros:', error);
        throw error;
    }
}


btnStart.addEventListener("click", async () => {
    clearInfo();
    labelWaiting.style.display = "block";

    try {
        const capture = await captureFrame();
        console.log('Longitud de los bytes de la imagen:', capture.size); // Asegúrate de que capture sea realmente un Blob
        const result = await detectFace(await capture.arrayBuffer());
    } catch (error) {
        console.log(error);
    } finally {
        labelWaiting.style.display = "none";
    }
});


async function updateUIWithInfanteInfo(infanteId) {
    try {
        const infanteInfoFromDB = await getInfanteInfoFromDatabase(infanteId);
        
        if (infanteInfoFromDB.nombre && infanteInfoFromDB.edad && infanteInfoFromDB.sala) {
            changeTextContainerPerson(`${infanteInfoFromDB.nombre}, Edad: ${infanteInfoFromDB.edad} años, Sala: ${infanteInfoFromDB.sala}`);
            labelWaiting.style.display = "none";
            changeTextAlertAnalized("alert alert-success", "Aceptado");
            setTimeout( ()=> {
                labelRedirect.style.display = "none";
                 window.location.href = `/evaluar/${infanteId}`;
            }, 2000);
        }
    } catch (error) {
        console.error('Error al obtener información del infante desde la base de datos:', error);
        // Manejar el error según sea necesario
    }
}
function updateUIWithNoMatch() {
    labelWaiting.style.display = "none";
    changeTextAlertAnalized("alert alert-danger", "Rechazado");
}

function getInfanteIdFromResult(result) {
    if (result.FaceMatches && result.FaceMatches.length > 0) {
        const matchedFace = result.FaceMatches[0].Face;

        if (matchedFace) {
            return matchedFace.ExternalImageId;
        }
    }
    return null;
}

