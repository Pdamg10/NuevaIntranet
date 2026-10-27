// procesador_worker.js
// Este Worker recibe el contenido del CSV (como string) y construye el mapa optimizado.

const DELIMITER = ';';

// Función para parsear el CSV y crear el Mapa (Tabla Hash)
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

        const cedula = String(rowObject['cedula'] || '').trim();
        const ficha = String(rowObject['ficha'] || '').trim();
        
        // Creamos una entrada en el Map tanto por cédula como por ficha
        if (cedula) {
            tempMap.set(cedula, rowObject);
        }
        if (ficha && ficha !== cedula) {
            tempMap.set(ficha, rowObject);
        }
    });

    return tempMap;
}

// Escucha mensajes del hilo principal (recibe el texto del CSV)
self.onmessage = function(event) {
    const csvContent = event.data.csvContent;
    
    try {
        // Ejecutar el procesamiento pesado en segundo plano
        const dataMap = parseCSV(csvContent);

        // Enviar el Map de vuelta al hilo principal para las búsquedas O(1)
        self.postMessage({
            status: 'success',
            dataMap: Array.from(dataMap.entries()), 
            size: dataMap.size
        });

    } catch (error) {
        self.postMessage({
            status: 'error',
            message: "Error al procesar el contenido CSV: " + error.message
        });
    }
};