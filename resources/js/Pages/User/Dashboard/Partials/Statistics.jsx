import { Column, TinyArea } from "@ant-design/charts";
import { Progress } from "antd";
import React, { useState } from "react";

const StatisticsChart = ({ data, color, lineColor }) => {
    const config = {
        height: 60,
        autoFit: false,
        data,
        smooth: true,
        areaStyle: {
            fill: color,
        },
        line: {
            color: lineColor,
        },
    };
    return <TinyArea {...config} />;
};

const DemoColumn = () => {
    const data = [
        {
            name: "London",
            month: "Jan.",
            average_monthly_rainfall: 18.9,
        },
        {
            name: "London",
            month: "Feb.",
            average_monthly_rainfall: 28.8,
        },
        {
            name: "London",
            month: "Mar.",
            average_monthly_rainfall: 39.3,
        },
        {
            name: "London",
            month: "Apr.",
            average_monthly_rainfall: 81.4,
        },
        {
            name: "London",
            month: "May",
            average_monthly_rainfall: 47,
        },
        {
            name: "London",
            month: "Jun.",
            average_monthly_rainfall: 20.3,
        },
        {
            name: "London",
            month: "Jul.",
            average_monthly_rainfall: 24,
        },
        {
            name: "London",
            month: "Aug.",
            average_monthly_rainfall: 35.6,
        },
        {
            name: "Berlin",
            month: "Jan.",
            average_monthly_rainfall: 12.4,
        },
        {
            name: "Berlin",
            month: "Feb.",
            average_monthly_rainfall: 23.2,
        },
        {
            name: "Berlin",
            month: "Mar.",
            average_monthly_rainfall: 34.5,
        },
        {
            name: "Berlin",
            month: "Apr.",
            average_monthly_rainfall: 99.7,
        },
        {
            name: "Berlin",
            month: "May",
            average_monthly_rainfall: 52.6,
        },
        {
            name: "Berlin",
            month: "Jun.",
            average_monthly_rainfall: 35.5,
        },
        {
            name: "Berlin",
            month: "Jul.",
            average_monthly_rainfall: 37.4,
        },
        {
            name: "Berlin",
            month: "Aug.",
            average_monthly_rainfall: 42.4,
        },
        {
            name: "x",
            month: "Jan.",
            average_monthly_rainfall: 42.4,
        },
    ];
    const config = {
        data,
        isGroup: true,
        xField: "month",
        yField: "average_monthly_rainfall",
        seriesField: "name",

        /** 设置颜色 */
        color: ["#4B5F8C", "#F16565", "#F9B035"],
        columnStyle: {
            radius: [5, 5, 0, 0],
            border: "0",
        },

        /** 设置间距 */
        // marginRatio: 0.1,
        label: {
            position: "top",
            layout: [
                {
                    type: "interval-adjust-position",
                },
                {
                    type: "interval-hide-overlap",
                },
                {
                    type: "adjust-color",
                },
            ],
        },
    };
    return <Column {...config} />;
};

const Statistics = () => {

    const [statistics, setStatistics] = useState([
        {
            name: "All Projects",
            count: "320 Project",
            lineColor: "#4B5F8C",
            color: "#F4F6FA",
        },
        {
            name: "Projects Complete",
            count: "12 Project",
            lineColor: "#40C694",
            color: "#EEFDF6",
        },
        {
            name: "Project Waiting",
            count: "6 Project",
            lineColor: "#FBD087",
            color: "#FFFAEF",
        },
        {
            name: "Absent Days",
            count: "20 Days",
            lineColor: "#F16565",
            color: "#FEF3F0",
        },
    ]);
    return (
        <>
            <div className="grid grid-cols-2 gap-4">
                <div className="grid grid-cols-2 gap-4">
                    {statistics.map((statistic, index) => {
                        return (
                            <div
                                className="bg-white rounded-md p-3"
                                key={index}
                            >
                                <div className="flex flex-col gap-2">
                                    <div>
                                        <span className="font-semibold block mb-2">
                                            {statistic.name}
                                        </span>
                                        <span className="text-gray-500">
                                            {statistic.count}
                                        </span>
                                    </div>
                                    <StatisticsChart
                                        data={[
                                            264, 417, 438, 887, 309, 397, 550,
                                            575, 563, 430, 525, 592, 492, 467,
                                            513, 546, 983, 340, 539, 243, 226,
                                            192,
                                        ]}
                                        color={statistic.color}
                                        lineColor={statistic.lineColor}
                                    />
                                </div>
                            </div>
                        );
                    })}
                </div>
                <div className="bg-white h-full p-4 rounded-md">
                    <DemoColumn />
                </div>
            </div>
            <div className="grid grid-cols-3 gap-4">
                <div className="col-span-2 grid grid-cols-3 gap-4">
                    <div className="bg-white py-3 px-4 rounded-md flex items-center gap-2">
                        <Progress
                            type="circle"
                            percent={90}
                            strokeColor={{ color: "#40C694" }}
                            width={60}
                        />
                        <div>
                            <span className="block text-lg font-semibold">
                                Weekly Target
                            </span>
                            <span className="text-sm font-medium text-green-500">
                                25%
                            </span>
                        </div>
                    </div>
                    <div className="bg-white py-3 px-4 rounded-md flex items-center gap-2">
                        <Progress
                            type="circle"
                            percent={50}
                            strokeColor={{ color: "#40C694" }}
                            width={60}
                        />
                        <div>
                            <span className="block text-lg font-semibold">
                                Monthly Target
                            </span>
                            <span className="text-sm font-medium text-green-500">
                                50%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
};

export default Statistics;
