# Contenido del blog

Aquí se guarda el contenido de las entradas para que quede en Git. Cada integrante escribe su parte en un archivo JSON, hace commit y luego lo carga a la base con un comando.

## Cómo agregar contenido

1. Copia `_ejemplo.json` y renómbralo, por ejemplo `dia-1.json`. Puedes tener un archivo por día o uno solo con todas las entradas.
2. Escribe la información de cada actividad dentro de la lista `entradas`.
3. Guarda las fotos en `contenido/imagenes/`.
4. Haz commit de tus archivos.
5. Carga el contenido a la base desde la carpeta del repositorio:

```bash
docker compose exec -u www-data -w /var/www/html/blog-semana-2026 servidor \
  php artisan contenido:importar
```

Para cargar un solo archivo:

```bash
docker compose exec -u www-data -w /var/www/html/blog-semana-2026 servidor \
  php artisan contenido:importar contenido/dia-1.json
```

Los archivos que empiezan con `_` no se importan. Por eso `_ejemplo.json` solo sirve de guía.

## Campos de cada entrada

| Campo | Obligatorio | Qué lleva |
|---|---|---|
| `slug` | No | Identifica la entrada. Si no lo pones, se genera del título. No lo cambies después de publicar. |
| `titulo` | Sí | Título de la entrada. |
| `categoria` | Sí | Nombre o slug de una categoría existente. |
| `fecha` | Sí | Fecha de la actividad, en formato `2026-11-16`. |
| `estado` | No | `borrador` o `publicada`. Si se omite, queda como borrador. |
| `resumen` | No | Texto corto para las tarjetas del blog. |
| `expositor` | No | Quién dio la ponencia o dirigió la actividad. |
| `desarrollo` | Sí | Qué se hizo durante la actividad. |
| `aprendizajes` | Sí | Lo que aprendieron o los resultados. |
| `reflexion` | Sí | Reflexión personal del equipo. |
| `autores` | Sí | Correos de los integrantes que firman la entrada. |
| `portada` | No | Ruta de la imagen principal, por ejemplo `imagenes/portada.jpg`. |
| `galeria` | No | Lista de rutas de imágenes adicionales, máximo 6. |

Categorías disponibles: `Ponencias`, `Talleres`, `Bingo`, `Feria de proyectos`, `Comelona de pizza`, `Fútbol sala` y `Parrillada`.

Autores: `ym23003@ues.edu.sv`, `bl23012@ues.edu.sv` y `ml23011@ues.edu.sv`.

## Qué escribir según la actividad

| Actividad | Contenido mínimo |
|---|---|
| Ponencias | Tema, expositor, ideas centrales y aprendizajes |
| Talleres | Herramientas, procedimiento, práctica y resultados |
| Bingo | Dinámica, experiencia y participación |
| Feria de proyectos | Proyectos visitados, tecnologías e innovación |
| Comelona de pizza | Desarrollo, convivencia y experiencia |
| Fútbol sala | Desarrollo, equipos y participación |
| Parrillada | Descripción, convivencia y reflexión |

En `desarrollo` va la parte descriptiva. En `aprendizajes` y `reflexion` va lo que el equipo sacó de la actividad.

## Imágenes

- Van en `contenido/imagenes/`.
- Formatos: JPG, PNG o WebP.
- Máximo 4 MB por imagen.
- Para la portada usa `"portada": "imagenes/portada-dia1.jpg"`.
- Para la galería usa `"galeria": ["imagenes/foto1.jpg", "imagenes/foto2.jpg"]`.
- Si dejas `"portada": null`, la entrada conserva la portada que ya tenga.
- Si dejas `"galeria": []`, la galería actual se conserva.
- El importador nunca borra fotos. Solo agrega las que lista el JSON.

## Volver a importar

El `slug` es la clave: si vuelves a correr el comando con el mismo `slug`, la entrada se actualiza en vez de duplicarse. Puedes corregir el texto y volver a importar sin miedo.

El importador además nunca borra fotos. Si una entrada ya tiene portada o galería, subida desde el panel o de una importación anterior, se queda como está. Solo se agregan las fotos nuevas que liste el JSON.

Para quitar una foto de una entrada, se hace desde el panel de administración, no desde el JSON.
