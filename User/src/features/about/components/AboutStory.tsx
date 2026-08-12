import CountUp from '@/features/home/components/CountUp';

interface AboutStoryProps {
  storyImage: string | null;
}

export default function AboutStory({ storyImage }: AboutStoryProps) {
  const imageSrc = storyImage || 'https://picsum.photos/seed/aboutstory/600/400';

  return (
    <section className="story-section section-padding bg-light-alt">
      <div className="container">
        <div className="row align-items-center g-5">
          <div className="col-lg-6">
            <div className="story-image-wrapper">
              <img src={imageSrc} alt="Our Story" className="story-image" />
              <div className="story-image-accent"></div>
              <div className="story-floating-card">
                <i className="fa-sharp fa-solid fa-quote-right"></i>
                <span>&quot;Quality content, exceptional experience.&quot;</span>
              </div>
            </div>
          </div>
          <div className="col-lg-6">
            <span className="section-badge">Our Story</span>
            <h2 className="section-title">How LaraBlog <span className="text-gradient">Came to Life</span></h2>
            <p className="story-text">
              Founded in 2024, LaraBlog started as a small community of passionate writers. 
              Today, we host hundreds of articles across technology, design, business, and more. 
              Our commitment to quality and creativity has never wavered.
            </p>
            <p className="story-text">
              We believe in the power of words to change minds, spark conversations, and build connections. 
              Every piece of content on our platform is crafted with care.
            </p>
            <div className="story-stats">
              <div className="story-stat">
                <span className="story-stat-number">
                  <CountUp target={250} suffix="+" />
                </span>
                <span className="story-stat-label">Articles Published</span>
              </div>
              <div className="story-stat">
                <span className="story-stat-number">
                  <CountUp target={50} suffix="K+" />
                </span>
                <span className="story-stat-label">Monthly Readers</span>
              </div>
              <div className="story-stat">
                <span className="story-stat-number">
                  <CountUp target={15} suffix="+" />
                </span>
                <span className="story-stat-label">Contributors</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}