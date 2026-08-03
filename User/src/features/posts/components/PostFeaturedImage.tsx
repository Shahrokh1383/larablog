interface PostFeaturedImageProps {
  src: string;
  alt: string;
}

export default function PostFeaturedImage({ src, alt }: PostFeaturedImageProps) {
  return (
    <figure className="post-featured-image">
      <img src={src || `https://picsum.photos/seed/placeholder/1200/600`} alt={alt} className="img-fluid rounded-4 shadow-sm" />
      <figcaption>Photo by LaraBlog Studio</figcaption>
    </figure>
  );
}