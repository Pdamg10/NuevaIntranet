// csv_worker.js
// Script ejecutado en un hilo de fondo (Web Worker)

const DELIMITER = ';';

// Función para parsear el CSV y crear el Mapa (igual que antes)
function parseCSV(csvText) {
    const lines = csvText.split('\n').map(line => line.trim()).filter(line => line.length > 0);
    if (lines.length <= 1) return new Map();

    const headers = lines[0].split(DELIMITER).map(h => h.trim());
    const tempMap = new Map();

    lines.slice(1).forEach(line => {
        const values = line.split(DELIMITER).map(v => v.trim());
        if (values.length !== headers.length) return;

        const rowObject = {};
        headers.forEach((header, index) => {
            rowObject[header] = values[index];
        });

        const cedula = String(rowObject['cedula']).trim();
        const ficha = String(rowObject['ficha']).trim();
        
        if (cedula && cedula !== 'undefined') {
            tempMap.set(cedula, rowObject);
        }
        if (ficha && ficha !== 'undefined' && ficha !== cedula) {
            tempMap.set(ficha, rowObject);
        }
    });

    return tempMap;
}

// Escucha mensajes del hilo principal
self.onmessage = async function(event) {
    const csvFilePath = event.data.path;

    try {
        // 1. Descargar el archivo
        const response = await fetch(csvFilePath);
        if (!response.ok) {
            throw new Error(`Error ${response.status}: No se pudo acceder al archivo.`);
        }

        const csvText = await response.text();
        
        // 2. Procesar el archivo y construir el Map
        const dataMap = parseCSV(csvText);

        // 3. Enviar el Map de vuelta al hilo principal
        self.postMessage({
            status: 'success',
            dataMap: Array.from(dataMap.entries()), // Map se convierte a Array para la transferencia
            size: dataMap.size
        });

    } catch (error) {
        self.postMessage({
            status: 'error',
            message: error.message
        });
    }
};