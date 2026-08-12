import { Routes, Route, Navigate } from 'react-router-dom';
import { RequireAuth } from '@/shared/components/RequireAuth';
import { RequireAdmin } from '@/shared/components/RequireAdmin';
import AdminLayout from '@/shared/components/AdminLayout';
import LoginPage from '@/pages/Login';
import DashboardPage from '@/pages/Dashboard';
import UsersPage from '@/pages/Users';
import PostsPage from '@/pages/Posts';
import PostEditorPage from '@/pages/PostEditor';
import CategoriesPage from '@/pages/Categories';
import TagsPage from '@/pages/Tags';
import ProfilePage from '@/pages/Profile';
import PostCommentsPage from '@/pages/PostComments';
import SubscribersPage from '@/pages/Subscribers';
import ContactMessagesPage from '@/pages/ContactMessages';
import AboutPage from '@/pages/About';

export default function AppRouter() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      
      <Route
        element={
          <RequireAuth>
            <AdminLayout />
          </RequireAuth>
        }
      >
        <Route path="/dashboard" element={<DashboardPage />} />
        <Route path="/posts" element={<PostsPage />} />
        <Route path="/posts/editor" element={<PostEditorPage />} />
        <Route path="/posts/editor/:id" element={<PostEditorPage />} />
        <Route path="/categories" element={<CategoriesPage />} />
        <Route path="/tags" element={<TagsPage />} />
        <Route path="/profile" element={<ProfilePage />} />
        <Route path="/posts/:postId/comments" element={<PostCommentsPage />} />
        
        <Route
          path="/users"
          element={
            <RequireAdmin>
              <UsersPage />
            </RequireAdmin>
          }
        />
        <Route
          path="/subscribers"
          element={<RequireAdmin><SubscribersPage /></RequireAdmin>}
        />
        <Route
          path="/contact-messages"
          element={<RequireAdmin><ContactMessagesPage /></RequireAdmin>}
          />

          <Route
            path="/about"
            element={
              <RequireAdmin>
                <AboutPage />
              </RequireAdmin>
            }
          />
      </Route>
      
      <Route path="*" element={<Navigate to="/dashboard" replace />} />
    </Routes>
  );
}