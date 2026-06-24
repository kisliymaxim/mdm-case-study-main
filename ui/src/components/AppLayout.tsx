import { LayoutDashboard, Laptop, LucideIcon, Users } from 'lucide-react';
import { NavLink, Outlet } from 'react-router-dom';

function NavItem({
    to,
    icon: Icon,
    children,
}: {
    to: string;
    icon: LucideIcon;
    children: React.ReactNode;
}) {
    return (
        <NavLink
            to={to}
            end={to === '/'}
            className={({ isActive }) =>
                `inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-sm font-medium transition ${
                    isActive
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
                }`
            }
        >
            <Icon className="h-4 w-4" />
            {children}
        </NavLink>
    );
}

export default function AppLayout() {
    return (
        <div className="min-h-screen bg-slate-50 text-slate-900">
            <nav className="bg-white border-b border-slate-200 sticky top-0 z-30">
                <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16 items-center">
                        <div className="flex items-center gap-6">
                            <div className="flex items-center gap-2">
                                <div className="h-7 w-7 rounded-md bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">
                                    W
                                </div>
                                <span className="font-semibold text-slate-800">WorkWize MDM</span>
                            </div>
                            <div className="flex items-center gap-1">
                                <NavItem to="/" icon={LayoutDashboard}>
                                    Home
                                </NavItem>
                                <NavItem to="/assets" icon={Laptop}>
                                    Assets
                                </NavItem>
                                <NavItem to="/employees" icon={Users}>
                                    Employees
                                </NavItem>
                            </div>
                        </div>
                        <a
                            href="https://www.linkedin.com/in/kisliymaxim/"
                            target="_blank"
                            rel="noreferrer noopener"
                            className="text-xs text-slate-500 hover:text-slate-700"
                        >
                            by Maksym Kyslyi
                        </a>
                    </div>
                </div>
            </nav>

            <main className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <Outlet />
            </main>
        </div>
    );
}
