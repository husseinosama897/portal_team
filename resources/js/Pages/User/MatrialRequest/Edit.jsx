import React, { useEffect, useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {
    Avatar,
    Button,
    Comment,
    DatePicker,
    message,
    Space,
    Table,
    Tooltip,
    Upload,
} from "antd";
import { InboxOutlined } from "@ant-design/icons";
import { useForm } from "@inertiajs/react";
import axios from "axios";
import { motion } from "framer-motion";
import moment from "moment";

const Edit = (props) => {
    const { Dragger } = Upload;
    const [fileList, setFileList] = useState([]);
    const [selectUser, setSelectUser] = useState([]);

    const [selectedUsers, setSelectedUsers] = useState([]);
    const [rowsOfTable, setRowsOfTable] = useState([]);
    const [items, setItems] = useState(props.data.attributes);
    const { data, setData, post, processing, errors, reset } = useForm({
        attributes: items,
        content: props.data.content,
        date: props.data.date,
        files: props.data.files,
        project_id: props.data.project_id,
        ref: props.data.ref,
        subject: props.data.subject,
        to: props.data.to,
        cc: "",
    });

    const propsOfUploadFiles = {
        multiple: true,
        onRemove: (file) => {
            const index = fileList.indexOf(file);
            const newFileList = fileList.slice();
            newFileList.splice(index, 1);
            setFileList(newFileList);
        },
        beforeUpload: (file) => {
            setFileList([...fileList, file]);
            return false;
        },
        fileList,
        onDrop(e) {
            console.log("Dropped files", e.dataTransfer.files);
        },
    };

    const getUser = (cc = "") => {
        axios({
            url: "/user/userautocomplete",
            method: "post",
            data: {
                name: cc,
            },
        }).then((res) => {
            setSelectUser(res.data.data);
        });
    };

    const deleteUser = (id) => {
        console.log(id);
        setSelectedUsers((prev) => {
            return prev.filter((user, index) => user.id !== id);
        });
    };

    const addItem = () => {
        setItems((prev) => {
            return [
                ...prev,
                {
                    name: "",
                    unit: "",
                    qty: "",
                },
            ];
        });
    };

    const deleteItem = (id) => {
        setItems((prev) => {
            return prev.filter((item, index) => index !== id);
        });
    };

    const editItem = (event, id) => {
        const editItems = items.map((item, index) => {
            if (index === id) {
                item[event.target.name] = event.target.value;
            }
            return item;
        });
        setItems(editItems);
    };

    const submit = () => {
        const formData = new FormData();
        fileList.forEach((file) => {
            formData.append("files[]", file);
        });
    };

    useEffect(() => {
        getUser(data.cc);
    }, [data.cc]);

    useEffect(() => {
        setRowsOfTable([]);
        if (selectedUsers.length > 0) {
            selectedUsers.map((user, index) => {
                setRowsOfTable((prev) => {
                    return [
                        ...prev,
                        {
                            key: user.id,
                            name: user.name,
                            email: user.email,
                        },
                    ];
                });
            });
        }
    }, [selectedUsers]);

    return (
        <AuthenticatedLayout auth={props.auth} errors={props.errors}>
            <div className="space-y-2">
                <div className="flex items-center justify-between">
                    <span className="text-lg text-gray-700 font-semibold">
                        Create matrial request
                    </span>
                </div>
                <div className="bg-white p-4 rounded-md space-y-3">
                    <div className="grid grid-cols-4 gap-3">
                        <div>
                            <input
                                type="text"
                                className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                value={data.ref}
                                disabled
                                required
                            />
                        </div>
                        <div className="w-full h-full">
                            <DatePicker
                                style={{
                                    width: "100%",
                                    height: "100%",
                                    borderRadius: "8px",
                                }}
                                defaultValue={moment(data.date, "YYYY-MM-DD")}
                            />
                        </div>
                        <div>
                            <input
                                type="text"
                                className="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                placeholder="Subject"
                                value={data.subject}
                                required
                            />
                        </div>
                        <div>
                            <select
                                id="countries"
                                className="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 "
                                defaultValue={data.project_id}
                            >
                                <option>Choose a project</option>

                                {props.projects.map((project) => {
                                    return (
                                        <>
                                            <option
                                                value={project.id}
                                                key={project.id}
                                            >
                                                {project.name}
                                            </option>
                                        </>
                                    );
                                })}
                            </select>
                        </div>
                        <div>
                            <input
                                type="text"
                                className="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                placeholder="to"
                                value={data.to}
                                required
                            />
                        </div>
                        <div className="relative">
                            <input
                                type="text"
                                className="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                placeholder="cc"
                                value={data.cc}
                                onChange={(e) => setData("cc", e.target.value)}
                                required
                            />
                            {selectUser.length > 0 && data.cc !== "" ? (
                                <motion.div
                                    initial={{ y: "-19px" }}
                                    animate={{ y: "0" }}
                                    className="absolute w-full bg-white border mt-2 shadow-lg p-2 py-3 rounded-lg z-10"
                                >
                                    {selectUser?.map((user, index) => {
                                        return (
                                            <div
                                                key={index}
                                                className="cursor-pointer p-2 transition-all hover:bg-gray-100 rounded-md"
                                                onClick={() => {
                                                    setSelectedUsers((prev) => {
                                                        return [...prev, user];
                                                    });
                                                    data.cc = "";
                                                }}
                                            >
                                                {user.name}
                                            </div>
                                        );
                                    })}
                                </motion.div>
                            ) : null}
                        </div>
                    </div>
                    <div className="border">
                        <Table
                            size="small"
                            columns={[
                                {
                                    title: "Name",
                                    dataIndex: "name",
                                    key: "name",
                                    render: (text) => <a>{text}</a>,
                                },
                                {
                                    title: "Email",
                                    dataIndex: "email",
                                    key: "email",
                                },
                                {
                                    title: "Action",
                                    key: "action",
                                    render: (_, record) => (
                                        <Space size="middle">
                                            <Button
                                                type="primary"
                                                danger
                                                onClick={() =>
                                                    deleteUser(record.key)
                                                }
                                            >
                                                Delete
                                            </Button>
                                        </Space>
                                    ),
                                },
                            ]}
                            dataSource={rowsOfTable}
                            pagination={{ hideOnSinglePage: true }}
                        />
                    </div>
                    <div>
                        <label
                            htmlFor="message"
                            className="block mb-2 text-sm font-medium text-gray-900 "
                        >
                            Conent
                        </label>
                        <textarea
                            id="message"
                            rows="4"
                            className="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Write your thoughts here..."
                        ></textarea>
                    </div>
                </div>
                <div className="bg-white p-4 rounded-md">
                    <Dragger {...propsOfUploadFiles}>
                        <p className="ant-upload-drag-icon">
                            <InboxOutlined />
                        </p>
                        <p className="ant-upload-text">
                            Click or drag file to this area to upload
                        </p>
                        <p className="ant-upload-hint">
                            Support for a single or bulk upload. Strictly
                            prohibit from uploading company data or other band
                            files
                        </p>
                    </Dragger>
                </div>
                <div className="bg-white p-4 rounded-md space-y-4">
                    <div className="flex items-center justify-between">
                        <span className="text-lg">Items</span>
                        <Button
                            type="primary"
                            className="rounded-lg"
                            onClick={addItem}
                        >
                            Add
                        </Button>
                    </div>
                    <div className="grid grid-cols-6 gap-3">
                        {items.map((item, index) => {
                            return (
                                <>
                                    <div className="col-span-3 flex items-center gap-3">
                                        <div>
                                            <span className="p-2 block rounded-lg whitespace-nowrap">
                                                {index + 1} -{" "}
                                            </span>
                                        </div>
                                        <textarea
                                            name="name"
                                            rows="1"
                                            className="block p-2.5 w-full text-sm text-gray-900 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                            value={item.name}
                                            placeholder="Item description..."
                                            onChange={(e) => editItem(e, index)}
                                        ></textarea>
                                    </div>
                                    <div>
                                        <input
                                            name="unit"
                                            type="text"
                                            className="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                            placeholder="Unit"
                                            value={item.unit}
                                            required
                                            onChange={(e) => editItem(e, index)}
                                        />
                                    </div>
                                    <div>
                                        <input
                                            name="qty"
                                            type="number"
                                            className="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                            placeholder="Qty"
                                            value={item.qty}
                                            required
                                            onChange={(e) => editItem(e, index)}
                                        />
                                    </div>
                                    <div className="!h-full">
                                        <Button
                                            type="primary"
                                            danger
                                            className=" !px-4 "
                                            onClick={() => deleteItem(index)}
                                        >
                                            Delete
                                        </Button>
                                    </div>
                                </>
                            );
                        })}
                    </div>
                    <div className="ml-4 flex flex-col gap-3 space-y-2">
                        <span className="text-base text-gray-700 font-medium">
                            VAT : {props.data.vat}
                        </span>
                        <span className="text-base text-gray-700 font-medium">
                            Total amount : {props.data.total - props.data.vat}
                        </span>
                        <span className="text-base text-gray-700 font-medium">
                            Total amount + VAT : {props.data.total}
                        </span>
                    </div>
                </div>
                <div className="bg-white p-4 rounded-md space-y-4">
                    <span class="text-lg">Managers Comments</span>
                    <div>
                        {props.data.matrial_request_cycle.map((comment) => {
                            return (
                                <Comment
                                    author={<span>{comment.role.name}</span>}
                                    avatar={
                                        <Avatar
                                            src="https://joeschmoe.io/api/v1/random"
                                            alt="Han Solo"
                                        />
                                    }
                                    content={
                                        <p>
                                            We supply a series of design
                                            principles, practical patterns and
                                            high quality design resources
                                            (Sketch and Axure), to help people
                                            create their product prototypes
                                            beautifully and efficiently.
                                        </p>
                                    }
                                    datetime={
                                        <Tooltip title={moment(comment.created_at).format('LLL')}>
                                            <span>- {moment(comment.created_at, "YYYYMMDD").fromNow()}</span>
                                        </Tooltip>
                                    }
                                />
                            );
                        })}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
};

export default Edit;
