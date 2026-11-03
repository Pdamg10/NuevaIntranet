#!/usr/bin/env python3
"""
Genera data/cumpleaneros.json y assets/img/cumpleaneros/*.jpg a partir de DATA.csv y las fotos BMP.

Uso (desde la raíz del repo; requiere Pillow: pip install pillow):
    python tools/generar_cumpleaneros.py --csv RUTA/DATA.csv \
        --fotos RUTA/FotosAdabas RUTA/FotosAdabas/FotoBmp2

--fotos admite varias carpetas: para cada ficha se usa la primera que tenga su foto
(poner primero la de mayor resolución). Si ya existe el JSON, se conservan sus
campos "titulo" y "mensaje".

DATA.csv (windows-1252, separado por ;): FICHA;NOMBRES;APELLIDOS;AÑO NAC;MES NAC;DIA NAC;DPTO.;GCIA
El año de nacimiento NO se copia al JSON (solo se publican día y mes).
"""
import argparse, json, os, sys
from PIL import Image

TITULO = "¡Feliz Cumpleaños te desea CVG Alcasa!"
MENSAJE = ("En tu día especial, te deseamos lo mejor. Tu positividad y compromiso hacen que "
           "trabajar contigo sea un placer. ¡Por otro año de grandes logros juntos!")

def limpiar(s):
    return " ".join(s.split())

def leer_csv(ruta):
    texto = open(ruta, "rb").read().decode("cp1252")
    filas, omitidas = [], 0
    for linea in texto.splitlines():
        p = linea.split(";")
        if len(p) < 8 or not p[0].strip().isdigit():
            continue  # cabecera (ocupa varias líneas) o línea vacía
        try:
            mes, dia = int(p[4]), int(p[5])
            if not (1 <= mes <= 12 and 1 <= dia <= 31):
                raise ValueError
        except ValueError:
            omitidas += 1
            continue
        filas.append({"ficha": p[0].strip(), "nombre": limpiar(p[1] + " " + p[2]),
                      "departamento": limpiar(p[6]), "gerencia": limpiar(p[7]),
                      "dia": dia, "mes": mes})
    return filas, omitidas

def cuadrada(im):
    w, h = im.size
    lado = min(w, h)
    x, y = (w - lado) // 2, (h - lado) // 2
    return im.crop((x, y, x + lado, y + lado))

def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--csv", required=True)
    ap.add_argument("--fotos", nargs="+", required=True)
    ap.add_argument("--json", default="data/cumpleaneros.json")
    ap.add_argument("--salida-fotos", default="assets/img/cumpleaneros")
    a = ap.parse_args()

    filas, omitidas = leer_csv(a.csv)
    titulo, mensaje = TITULO, MENSAJE
    if os.path.exists(a.json):
        previo = json.load(open(a.json, encoding="utf-8"))
        titulo, mensaje = previo.get("titulo", titulo), previo.get("mensaje", mensaje)

    os.makedirs(a.salida_fotos, exist_ok=True)
    con_foto = 0
    for f in filas:
        for carpeta in a.fotos:
            origen = os.path.join(carpeta, f["ficha"] + ".bmp")
            if os.path.exists(origen):
                with Image.open(origen) as im:
                    cuadrada(im.convert("RGB")).save(
                        os.path.join(a.salida_fotos, f["ficha"] + ".jpg"),
                        "JPEG", quality=82, optimize=True, progressive=True)
                con_foto += 1
                break

    filas.sort(key=lambda f: (f["mes"], f["dia"], f["nombre"]))
    os.makedirs(os.path.dirname(a.json) or ".", exist_ok=True)
    with open(a.json, "w", encoding="utf-8", newline="\n") as fh:
        fh.write("{\n")
        fh.write('  "titulo": %s,\n' % json.dumps(titulo, ensure_ascii=False))
        fh.write('  "mensaje": %s,\n' % json.dumps(mensaje, ensure_ascii=False))
        fh.write('  "cumpleaneros": [\n')
        fh.write(",\n".join("    " + json.dumps(f, ensure_ascii=False) for f in filas))
        fh.write("\n  ]\n}\n")
    print(f"{len(filas)} trabajadores ({omitidas} filas omitidas) · {con_foto} con foto · "
          f"{len(filas) - con_foto} sin foto (usarán silueta)")

if __name__ == "__main__":
    sys.exit(main())
