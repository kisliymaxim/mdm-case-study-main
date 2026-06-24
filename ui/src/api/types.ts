export type Employee = {
    id: number;
    email: string;
    name: string | null;
    phone: string | null;
};

export type EmployeeWithCount = Employee & {
    assets_count: number;
};

export type AssetSummary = {
    id: number;
    serial_code: string;
    device_name: string;
    provider: string;
    employee_id: number;
    employee: Pick<Employee, 'id' | 'email' | 'name'> | null;
};

export type AssetDetail = {
    id: number;
    serial_code: string;
    device_name: string;
    provider: string;
    specs: Record<string, string | number | null> | null;
    employee: Employee | null;
};

export type ImportStatus = 'queued' | 'running' | 'succeeded' | 'failed';

export type MdmImport = {
    id: string;
    provider: string;
    status: ImportStatus;
    summary: Record<string, unknown> | null;
    error: string | null;
    started_at: string | null;
    finished_at: string | null;
};
