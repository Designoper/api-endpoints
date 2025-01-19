// Define la URL de tu endpoint y el token de autenticación
const url = 'https://tuservidor.com/api/tu-endpoint';
const token = 'tu_token_de_autenticacion';

// Función para realizar una solicitud POST
async function hacerSolicitudPost(datos) {
    const respuesta = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': token // Incluye el token en el encabezado
        },
        body: JSON.stringify(datos) // Convertir los datos a JSON
    });

    if (respuesta.ok) {
        const datosRespuesta = await respuesta.json();
        console.log('Respuesta:', datosRespuesta);
    } else {
        console.log('Error en la solicitud:', respuesta.status);
    }
}

// Ejemplo de datos a enviar
const datos = {
    nombre: 'Juan',
    edad: 30
};

// Realizar la solicitud POST
hacerSolicitudPost(datos);
