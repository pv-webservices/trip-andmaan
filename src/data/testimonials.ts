export interface Testimonial {
  name: string;
  text: string;
  approved: boolean;
  rating?: number;
  photo?: string;
  source?: string;
}
// Add only genuine client-approved reviews, with permission for names/photos.
export const testimonials: Testimonial[] = [];
