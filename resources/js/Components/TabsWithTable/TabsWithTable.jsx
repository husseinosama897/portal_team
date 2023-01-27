import { Pagination, Space, Steps, Table, Tabs, Tag } from "antd";
import axios from "axios";
import React, { useEffect, useState } from "react";
import Modal from "../Modal";
import { stateOfWorkFlow } from "@/Components/States";
import { Link } from "@inertiajs/react";

const TableOfContents = ({
    data,
    setPageNumber,
    total = 1,
    pageSize = 1,
    workflow,
    columnsOfTable
}) => {
    const { Step } = Steps;

    const [cylce, setCylce] = useState([]);

    const [status, setStatus] = useState(0);

    const [isModalOpen, setIsModalOpen] = useState(false);

    const [rows, setRows] = useState([]);

    const onChange = (e) => {
        setPageNumber(e);
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
        data?.map((item) => {
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
    }, [data]);

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
        <div className="space-y-4">
            <Table
                columns={columnsOfTable}
                dataSource={rows}
                pagination={{ hideOnSinglePage: true }}
            />
            <Pagination
                style={{ color: "#000" }}
                showQuickJumper
                defaultCurrent={1}
                total={total}
                pageSize={pageSize}
                onChange={onChange}
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
                    {workflow?.flowwork_step?.map((step) => {
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
    );
};

const TabsWithTable = ({ urls, labelsOfTabs, urlData, url }) => {
    const [pageNumber, setPageNumber] = useState(1);
    const [selectedTab, setSelectedTab] = useState(url);
    const [data, setData] = useState([]);

    const onChange = (key) => {
        setSelectedTab(urls[key]);
    };

    const fetcher = async (url) => {
        const res = await axios
            .get(url + `?page=${pageNumber}`)
            .then((res) => res);
        setData(res.data);
    };

    useEffect(() => {
        fetcher(selectedTab);
    }, [selectedTab, pageNumber]);

    return (
        <Tabs defaultActiveKey="0" onChange={onChange}>
            {labelsOfTabs.map((label) => {
                return (
                    <Tabs.TabPane tab={label.name} key={label.key}>
                        <TableOfContents
                            data={data?.data?.data}
                            setPageNumber={setPageNumber}
                            total={data?.data?.total}
                            pageSize={data?.data?.per_page}
                            workflow={data?.workflow}
                            columnsOfTable={label.columnsOfTable}
                        />
                    </Tabs.TabPane>
                );
            })}
        </Tabs>
    );
};

export default TabsWithTable;
