import React, { useEffect, useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Button, Modal, Pagination, Space, Steps, Table, Tag } from "antd";
import { stateOfWorkFlow } from "@/Components/States";
import { router, Link } from "@inertiajs/react";
import { showDeleteConfirm } from "@/Components/ModalDelete";

const Index = (props) => {
    const { Step } = Steps;
    const [cylce, setCylce] = useState([]);
    const [status, setStatus] = useState(0);
    const [isModalOpen, setIsModalOpen] = useState(false);

    const [rows, setRows] = useState([]);
    const onChange = (e) => {
        const url =
            route().t.url + "/" + route().t.routes[route().current()].uri+"?page="+e;

            router.get(url)
    };
    const handleCancel = () => {
        setIsModalOpen(false);
    };
    const handleOk = () => {
        setIsModalOpen(false);
    };
    const showModal = () => {
        setIsModalOpen(true);
    };
    useEffect(() => {
        setRows([]);
        props.data.data.map((item) => {
            setRows((prev) => {
                return [
                    ...prev,
                    {
                        key: item.id,
                        code: item.ref,
                        date: item.date,
                        description: item.content,
                        status: item.status,
                        project: item.project_id,
                        cylce: item.matrial_request_cycle,
                    },
                ];
            });
        });
    }, []);
    useEffect(() => {
        cylce?.map((step) => {
            if (step.status == 0) {
                setStatus("process");
            } else if (step.status == 1) {
                setStatus("finish");
            } else if (step.status == 2) {
                setStatus("error");
            } else if (step.status == 3) {
                setStatus("wait");
            }
        });
    }, [cylce]);
    return (
        <AuthenticatedLayout auth={props.auth} errors={props.errors}>
            <div className="space-y-3">
                <div className="flex items-center justify-between">
                    <span className="text-lg text-gray-700 font-semibold">
                        Matrial request
                    </span>
                    <Link href="/user/create_matrial_request">
                        <Button type="primary">Create</Button>
                    </Link>
                </div>
                <Table
                    columns={[
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
                            width: "40%",
                            render: (_, { description }) => (
                                <>
                                    <p
                                        style={{
                                            display: "-webkit-box",
                                            WebkitLineClamp: 1,
                                            WebkitBoxOrient: "vertical",
                                            overflow: "hidden",
                                            width: "70%",
                                        }}
                                    >
                                        {description}
                                    </p>
                                </>
                            ),
                        },
                        {
                            title: "Status",
                            dataIndex: "status",
                            key: "status",
                            render: (_, { status, cylce }) => (
                                <>
                                    {status !== null && (
                                        <Tag
                                            color={
                                                stateOfWorkFlow[status].color
                                            }
                                            onClick={() => {
                                                showModal(), setCylce(cylce);
                                            }}
                                            style={{
                                                borderRadius: "999px",
                                                cursor: "pointer",
                                            }}
                                        >
                                            {stateOfWorkFlow[status].name}
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
                                    <Link href={`/user/matrial_request/edit/${record.key}`} className="flex items-center text-blue-500 hover:text-blue-700">
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
                                    <button
                                        className="flex items-center text-red-500 hover:text-red-700"
                                        onClick={() =>
                                            showDeleteConfirm(
                                                "/user/delete_matrial_request_data/",
                                                record.key
                                            )
                                        }
                                    >
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
                                    </button>
                                </Space>
                            ),
                        },
                    ]}
                    dataSource={rows}
                    pagination={{ hideOnSinglePage: true }}
                />
                <Pagination
                    style={{ color: "#000" }}
                    showQuickJumper
                    defaultCurrent={1}
                    total={props.data.total}
                    pageSize={props.data.per_page}
                    onChange={onChange}
                    current={props.data.current_page}
                />
                <Modal
                    title="Workflow"
                    open={isModalOpen}
                    onOk={handleOk}
                    onCancel={handleCancel}
                    width={1024}
                >
                    <Steps
                        current={cylce.length > 0 ? cylce.length - 1 : ""}
                        status={status}
                    >
                        {props.workflow.flowwork_step.map((step) => {
                            return (
                                <Step
                                    title={step.role.name}
                                    key={`items-${step.id}`}
                                />
                            );
                        })}
                    </Steps>
                </Modal>
            </div>
        </AuthenticatedLayout>
    );
};

export default Index;
