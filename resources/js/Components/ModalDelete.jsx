import React from "react";
import { Button, Modal, Space } from "antd";
import { ExclamationCircleFilled } from "@ant-design/icons";
import axios from "axios";
import { router } from "@inertiajs/react";

const { confirm } = Modal;
export const showDeleteConfirm = (url, id) => {
    confirm({
        title: "Are you sure delete this?",
        icon: <ExclamationCircleFilled />,
        okText: "Yes",
        okType: "danger",
        cancelText: "No",
        onOk() {
            axios.post(url + id).then(res => {
                router.reload()
            });

        },
        onCancel() {
            console.log("Cancel");
        },
    });
};
