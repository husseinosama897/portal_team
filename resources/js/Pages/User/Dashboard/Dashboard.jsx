import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link } from "@inertiajs/react";
import { Space, Table, Tag } from "antd";
import Statistics from "./Partials/Statistics";

export default function Dashboard(props) {
    const columns = [
        {
            title: "Code",
            dataIndex: "code",
            key: "code",
        },
        {
            title: "Date",
            dataIndex: "date",
            key: "date",
        },
        {
            title: "Description",
            dataIndex: "description",
            key: "description",
        },
        {
            title: "Status",
            dataIndex: "status",
            key: "status",
            render: (_, { status }) => (
                <>
                    {status.toLowerCase() == "active" ? (
                        <Tag color={"green"} style={{ borderRadius: "999px" }}>
                            {status.toUpperCase()}
                        </Tag>
                    ) : (
                        <Tag color={"red"} style={{ borderRadius: "999px" }}>
                            {status.toUpperCase()}
                        </Tag>
                    )}
                </>
            ),
        },
        {
            title: "Project",
            dataIndex: "project",
            key: "project",
        },
        {
            title: "Action",
            dataIndex: "action",
            key: "action",
            render: (_, record) => (
                <Space size="middle">
                    <Link className="flex items-center text-blue-500 hover:text-blue-700">
                        <svg
                            className="w-4 h-4 mr-1 rtl:ml-1"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                        </svg>
                        Edit
                    </Link>
                    <Link className="flex items-center text-red-500 hover:text-red-700">
                        <svg
                            className="w-4 h-4 mr-1 rtl:ml-1"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path d="M21 6.73001C20.98 6.73001 20.95 6.73001 20.92 6.73001C15.63 6.20001 10.35 6.00001 5.12 6.53001L3.08 6.73001C2.66 6.77001 2.29 6.47001 2.25 6.05001C2.21 5.63001 2.51 5.27001 2.92 5.23001L4.96 5.03001C10.28 4.49001 15.67 4.70001 21.07 5.23001C21.48 5.27001 21.78 5.64001 21.74 6.05001C21.71 6.44001 21.38 6.73001 21 6.73001Z" />
                            <path d="M8.5 5.72C8.46 5.72 8.42 5.72 8.37 5.71C7.97 5.64 7.69 5.25 7.76 4.85L7.98 3.54C8.14 2.58 8.36 1.25 10.69 1.25H13.31C15.65 1.25 15.87 2.63 16.02 3.55L16.24 4.85C16.31 5.26 16.03 5.65 15.63 5.71C15.22 5.78 14.83 5.5 14.77 5.1L14.55 3.8C14.41 2.93 14.38 2.76 13.32 2.76H10.7C9.64 2.76 9.62 2.9 9.47 3.79L9.24 5.09C9.18 5.46 8.86 5.72 8.5 5.72Z" />
                            <path d="M15.21 22.75H8.79C5.3 22.75 5.16 20.82 5.05 19.26L4.4 9.19001C4.37 8.78001 4.69 8.42001 5.1 8.39001C5.52 8.37001 5.87 8.68001 5.9 9.09001L6.55 19.16C6.66 20.68 6.7 21.25 8.79 21.25H15.21C17.31 21.25 17.35 20.68 17.45 19.16L18.1 9.09001C18.13 8.68001 18.49 8.37001 18.9 8.39001C19.31 8.42001 19.63 8.77001 19.6 9.19001L18.95 19.26C18.84 20.82 18.7 22.75 15.21 22.75Z" />
                            <path d="M13.66 17.25H10.33C9.92 17.25 9.58 16.91 9.58 16.5C9.58 16.09 9.92 15.75 10.33 15.75H13.66C14.07 15.75 14.41 16.09 14.41 16.5C14.41 16.91 14.07 17.25 13.66 17.25Z" />
                            <path d="M14.5 13.25H9.5C9.09 13.25 8.75 12.91 8.75 12.5C8.75 12.09 9.09 11.75 9.5 11.75H14.5C14.91 11.75 15.25 12.09 15.25 12.5C15.25 12.91 14.91 13.25 14.5 13.25Z" />
                        </svg>
                        Delete
                    </Link>
                </Space>
            ),
        },
    ];
    const data = [
        {
            key: "1",
            code: "MR-15",
            date: "2022-11-29",
            description: "Plumbing tools",
            status: "Active",
            project: "CP-HO",
        },
        {
            key: "2",
            code: "MR-15",
            date: "2022-11-29",
            description: "Plumbing tools",
            status: "Active",
            project: "CP-HO",
        },
        {
            key: "3",
            code: "MR-15",
            date: "2022-11-29",
            description: "Plumbing tools",
            status: "x",
            project: "CP-HO",
        },
    ];

    return (
        <AuthenticatedLayout
            auth={props.auth}
            errors={props.errors}
            header={
                <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />
            <div className="flex flex-col gap-6">
                <Statistics />
                <div className="space-y-3">
                    <div className="font-semibold text-lg text-gray-700 flex items-center gap-2">
                        <svg
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path d="M17.6201 9.62012H12.3701C11.9601 9.62012 11.6201 9.28012 11.6201 8.87012C11.6201 8.46012 11.9601 8.12012 12.3701 8.12012H17.6201C18.0301 8.12012 18.3701 8.46012 18.3701 8.87012C18.3701 9.28012 18.0401 9.62012 17.6201 9.62012Z"></path>
                            <path d="M7.11957 10.3801C6.92957 10.3801 6.73957 10.3101 6.58957 10.1601L5.83957 9.41007C5.54957 9.12007 5.54957 8.64007 5.83957 8.35007C6.12957 8.06007 6.60957 8.06007 6.89957 8.35007L7.11957 8.57007L8.83957 6.85007C9.12957 6.56007 9.60957 6.56007 9.89957 6.85007C10.1896 7.14007 10.1896 7.62007 9.89957 7.91007L7.64957 10.1601C7.50957 10.3001 7.31957 10.3801 7.11957 10.3801Z"></path>
                            <path d="M17.6201 16.6201H12.3701C11.9601 16.6201 11.6201 16.2801 11.6201 15.8701C11.6201 15.4601 11.9601 15.1201 12.3701 15.1201H17.6201C18.0301 15.1201 18.3701 15.4601 18.3701 15.8701C18.3701 16.2801 18.0401 16.6201 17.6201 16.6201Z"></path>
                            <path d="M7.11957 17.3801C6.92957 17.3801 6.73957 17.3101 6.58957 17.1601L5.83957 16.4101C5.54957 16.1201 5.54957 15.6401 5.83957 15.3501C6.12957 15.0601 6.60957 15.0601 6.89957 15.3501L7.11957 15.5701L8.83957 13.8501C9.12957 13.5601 9.60957 13.5601 9.89957 13.8501C10.1896 14.1401 10.1896 14.6201 9.89957 14.9101L7.64957 17.1601C7.50957 17.3001 7.31957 17.3801 7.11957 17.3801Z"></path>
                            <path d="M15 22.75H9C3.57 22.75 1.25 20.43 1.25 15V9C1.25 3.57 3.57 1.25 9 1.25H15C20.43 1.25 22.75 3.57 22.75 9V15C22.75 20.43 20.43 22.75 15 22.75ZM9 2.75C4.39 2.75 2.75 4.39 2.75 9V15C2.75 19.61 4.39 21.25 9 21.25H15C19.61 21.25 21.25 19.61 21.25 15V9C21.25 4.39 19.61 2.75 15 2.75H9Z"></path>
                        </svg>
                        Lastest Material Request
                    </div>
                    <Table
                        columns={columns}
                        dataSource={data}
                        pagination={{ hideOnSinglePage: true }}
                    />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
