export type Category = "fotografia" | "video" | "arquitectura";

export interface Project {
  slug: string;
  title: string;
  category: Category;
  description: string;
  cover?: string;
  year: string;
  location: string;
}

export const projects: Project[] = [
  {
    slug: "amanecer-costa",
    title: "Amanecer en la costa",
    category: "fotografia",
    description: "Serie de fotografía aérea al amanecer. Proyecto de ejemplo.",
    year: "2025",
    location: "Costa Brava",
  },
  {
    slug: "retrato-aereo",
    title: "Retrato aéreo",
    category: "fotografia",
    description: "Sesión de retrato con dron. Proyecto de ejemplo.",
    year: "2025",
    location: "Barcelona",
  },
  {
    slug: "promo-inmobiliaria",
    title: "Promo inmobiliaria",
    category: "video",
    description: "Video promocional para inmobiliaria. Proyecto de ejemplo.",
    year: "2025",
    location: "Madrid",
  },
  {
    slug: "evento-deportivo",
    title: "Evento deportivo",
    category: "video",
    description: "Cobertura aérea de evento. Proyecto de ejemplo.",
    year: "2024",
    location: "Valencia",
  },
  {
    slug: "villa-moderna",
    title: "Villa moderna",
    category: "arquitectura",
    description: "Levantamiento visual de villa. Proyecto de ejemplo.",
    year: "2025",
    location: "Marbella",
  },
  {
    slug: "edificio-historico",
    title: "Edificio histórico",
    category: "arquitectura",
    description: "Documentación de patrimonio. Proyecto de ejemplo.",
    year: "2024",
    location: "Sevilla",
  },
];

export const getByCategory = (cat: Category) => projects.filter((p) => p.category === cat);
export const getBySlug = (cat: Category, slug: string) =>
  projects.find((p) => p.category === cat && p.slug === slug);
