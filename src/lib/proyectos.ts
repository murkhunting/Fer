export type Tag = "fotografia" | "video" | "arquitectura";

export interface Proyecto {
  slug: string;
  title: string;
  location: string;
  client: string;
  year: number;
  tags: Tag[];
  cover: string;
  gallery: string[];
  videoUrl: string;
  description: string;
}

export const TAGS: Tag[] = ["fotografia", "video", "arquitectura"];

export function porTag(all: Proyecto[], tag: Tag): Proyecto[] {
  return all.filter((p) => p.tags.includes(tag));
}

export function validarProyecto(p: Proyecto): string[] {
  const errores: string[] = [];
  if (!p.title?.trim()) errores.push("Falta el título.");
  if (!p.slug?.trim()) errores.push("Falta el slug.");
  if (!p.tags?.length) errores.push("Elige al menos una etiqueta.");
  const tieneMedia = p.tags.includes("fotografia") || p.tags.includes("video");
  if (!tieneMedia) errores.push('La etiqueta "arquitectura" sola no vale: añade foto o video.');
  if (p.tags.includes("fotografia") && !p.gallery?.length)
    errores.push("Si es de fotografía, la galería es obligatoria.");
  if (p.tags.includes("video") && !p.videoUrl?.trim())
    errores.push("Si es de video, la URL del video es obligatoria.");
  if (!p.cover?.trim()) errores.push("Falta la imagen principal.");
  return errores;
}

export function youtubeEmbed(url: string): string | null {
  if (!url) return null;
  const yt = url.match(/(?:youtube\.com\/(?:watch\?v=|shorts\/|embed\/)|youtu\.be\/)([\w-]{6,})/);
  if (yt) return `https://www.youtube.com/embed/${yt[1]}`;
  const vm = url.match(/vimeo\.com\/(\d+)/);
  if (vm) return `https://player.vimeo.com/video/${vm[1]}`;
  return null;
}

export function tagLabel(t: Tag): string {
  return t === "fotografia" ? "Foto" : t === "video" ? "Video" : "Arquitectura";
}
