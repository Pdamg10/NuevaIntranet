import os
import ollama

# Configuración del modelo local
MODEL_NAME = "qwen2.5-coder"

def analizar_archivo(ruta_archivo):
    if not os.path.exists(ruta_archivo):
        print(f"[-] El archivo '{ruta_archivo}' no existe.")
        return

    print(f"[*] Leyendo el archivo '{ruta_archivo}'...")
    with open(ruta_archivo, "r", encoding="utf-8") as f:
        codigo = f.read()

    prompt = (
        "Actúa como un arquitecto de software senior. Analiza el siguiente código, "
        "identifica posibles mejoras de rendimiento, errores lógicos, problemas de seguridad "
        "o fallas de diseño, y propón el código refactorizado y limpio:\n\n"
        f"```\n{codigo}\n```"
    )

    print(f"[*] Consultando a {MODEL_NAME} (esto puede tomar unos segundos)...")
    try:
        response = ollama.chat(
            model=MODEL_NAME,
            messages=[{"role": "user", "content": prompt}]
        )
        print("\n--- RESPUESTA DEL ASISTENTE LOCAL ---")
        print(response["message"]["content"])
        print("--------------------------------------\n")
    except Exception as e:
        print(f"[-] Error al conectar con Ollama: {e}")
        print("Asegúrate de que Ollama esté activo ejecutando: sudo systemctl start ollama")

if __name__ == "__main__":
    # Puedes cambiar esto por cualquier archivo de tu proyecto que quieras revisar
    archivo_a_revisar = input("Introduce la ruta del archivo que quieres analizar (ej: main.py): ").strip()
    analizar_archivo(archivo_a_revisar)