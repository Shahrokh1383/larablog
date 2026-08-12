const values = [
  {
    icon: 'fa-lightbulb',
    title: 'Innovation',
    desc: 'We constantly push the boundaries of content creation and platform design.',
  },
  {
    icon: 'fa-hand-holding-heart',
    title: 'Integrity',
    desc: 'We value honesty, transparency, and ethical content curation.',
  },
  {
    icon: 'fa-users',
    title: 'Community',
    desc: 'We foster a welcoming environment where voices are heard and respected.',
  },
];

export default function AboutValues() {
  return (
    <section className="values-section section-padding">
      <div className="container">
        <div className="section-header text-center">
          <span className="section-badge">Our Values</span>
          <h2 className="section-title">What <span className="text-gradient">Drives Us</span></h2>
          <p className="section-desc">These core principles guide everything we do</p>
        </div>
        <div className="row g-4">
          {values.map((value, index) => (
            <div className="col-lg-4 col-md-6" key={index}>
              <div className="value-card">
                <div className="value-icon">
                  <i className={`fa-sharp fa-solid ${value.icon}`}></i>
                </div>
                <h3 className="value-title">{value.title}</h3>
                <p className="value-desc">{value.desc}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}