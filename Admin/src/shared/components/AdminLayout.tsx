import { NavLink, useNavigate } from 'react-router-dom';
import { useAdminAuth } from '@/features/auth/hooks/useAdminAuth';
import { Outlet } from 'react-router-dom';

export default function AdminLayout() {
  const { user, logout } = useAdminAuth();
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  const handleProfileClick = () => {
    navigate('/profile');
  };

  const navItems = [
    { to: '/dashboard', label: 'Dashboard', icon: 'fa-tachometer-alt' },
    { to: '/posts', label: 'Posts', icon: 'fa-newspaper' },
    { to: '/categories', label: 'Categories', icon: 'fa-folder' },
    { to: '/tags', label: 'Tags', icon: 'fa-tags' },
  ];

  // Conditionally add User Management link for admins
  if (user?.roles.includes('admin')) {
    navItems.push({ to: '/users', label: 'User Management', icon: 'fa-users' });
    navItems.push({ to: '/subscribers', label: 'Subscribers', icon: 'fa-envelope-open-text' });
    navItems.push({ to: '/contact-messages', label: 'Messages', icon: 'fa-inbox' });
    navItems.push({ to: '/about', label: 'About Page', icon: 'fa-info-circle' }); 
  }

  return (
    <div className="d-flex" style={{ minHeight: '100vh' }}>
      {/* Sidebar */}
      <nav className="bg-dark text-white p-3 d-flex flex-column" style={{ width: '250px' }}>
        <div className="mb-4 text-center border-bottom pb-3">
          <h4 className="mb-0">Larablog Admin</h4>
        </div>
        
        <ul className="nav nav-pills flex-column mb-auto gap-2">
          {navItems.map((item) => (
            <li className="nav-item" key={item.to}>
              <NavLink 
                to={item.to} 
                className={({ isActive }) => 
                  `nav-link text-white d-flex align-items-center ${isActive ? 'bg-primary' : ''}`
                }
              >
                <i className={`fas ${item.icon} me-2`}></i> {item.label}
              </NavLink>
            </li>
          ))}
        </ul>

        <div className="mt-auto border-top pt-3">
          <div className="d-flex align-items-center mb-3">
            {/* Conditionally render Avatar Image or Text Initial */}
            {user?.avatar ? (
              <img 
                src={user.avatar} 
                alt={user.name}
                className="rounded-circle me-2" 
                style={{ width: 40, height: 40, objectFit: 'cover', cursor: 'pointer' }}
                onClick={handleProfileClick}
                title="View Profile"
              />
            ) : (
              <div 
                className="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" 
                style={{ width: 40, height: 40, cursor: 'pointer' }}
                onClick={handleProfileClick}
                title="View Profile"
              >
                {user?.name.charAt(0).toUpperCase()}
              </div>
            )}
            <div className="d-flex flex-column flex-grow-1">
              <div className="text-white small fw-bold">{user?.name}</div>
              <div className="d-flex gap-1 mt-1 flex-wrap">
                {user?.roles.map((role) => (
                  <span key={role} className="badge bg-info text-dark text-capitalize">{role}</span>
                ))}
              </div>
            </div>
            <button 
              onClick={handleProfileClick}
              className="btn btn-sm btn-outline-light ms-2"
              title="Profile Settings"
            >
              <i className="fas fa-cog"></i>
            </button>
          </div>
          <button onClick={handleLogout} className="btn btn-outline-light btn-sm w-100">
            <i className="fas fa-sign-out-alt me-2"></i>Logout
          </button>
        </div>
      </nav>

      {/* Main Content Area */}
      <div className="flex-grow-1 bg-light" style={{ overflowY: 'auto' }}>
        <Outlet />
      </div>
    </div>
  );
}