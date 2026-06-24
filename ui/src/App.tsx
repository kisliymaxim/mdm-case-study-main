import { Navigate, Route, Routes } from 'react-router-dom';

import AppLayout from '@/components/AppLayout';
import { ToastProvider } from '@/components/Toast';
import AssetDetail from '@/pages/AssetDetail';
import AssetsIndex from '@/pages/AssetsIndex';
import EmployeesIndex from '@/pages/EmployeesIndex';
import Home from '@/pages/Home';

export default function App() {
    return (
        <ToastProvider>
            <Routes>
                <Route element={<AppLayout />}>
                    <Route path="/" element={<Home />} />
                    <Route path="/assets" element={<AssetsIndex />} />
                    <Route path="/assets/:id" element={<AssetDetail />} />
                    <Route path="/employees" element={<EmployeesIndex />} />
                    <Route path="*" element={<Navigate to="/" replace />} />
                </Route>
            </Routes>
        </ToastProvider>
    );
}
