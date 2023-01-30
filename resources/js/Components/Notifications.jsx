import { Link } from "@inertiajs/react";
import moment from "moment";
import { motion } from "framer-motion";

const Notifications = ({ isOpen, setIsOpen }) => {
    const variants = {
        open: { x: 0 },
        closed: { x: "100%" },
    };
    return (
        <div className="relative z-50">
            <motion.div
                initial={"closed"}
                animate={
                    isOpen
                        ? { opacity: 1 }
                        : { opacity: 0, "aria-hidden": "true", display: "none" }
                }
                className="fixed inset-0 w-full h-full bg-black/50 cursor-pointer"
                aria-hidden="true"
                onClick={() => setIsOpen(false)}
            ></motion.div>
            <motion.div
                initial={"closed"}
                animate={isOpen ? "open" : "closed"}
                variants={variants}
                transition={{ ease: "easeOut", duration: 0.18 }}
                className={`scrollbar max-w-sm fixed right-0 top-0 bg-white h-full w-full z-40 overflow-y-auto `}
            >
                <div className="flex flex-col gap-1">
                    <div className="p-2 flex items-center justify-between">
                        <span className="block text-lg font-bold tracking-tight relative">
                            <span>Notifications</span>
                            <span className="inline-flex absolute items-center justify-center top-0 ml-1 min-w-[1rem] h-4 rounded-full text-xs text-red-700 bg-red-500/10">
                                3
                            </span>
                        </span>
                        <button
                            tabIndex="-1"
                            type="button"
                            className="rtl:right-auto transition-all p-1 rounded-lg hover:bg-gray-200"
                            onClick={() => setIsOpen(false)}
                        >
                            <svg
                                tabIndex="-1"
                                className="h-4 w-4 cursor-pointer text-gray-400"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    fillRule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clipRule="evenodd"
                                ></path>
                            </svg>
                            <span className="sr-only">Close</span>
                        </button>
                    </div>
                    <div className="flex flex-col">
                        {Array.of(
                            1,
                            2,
                            3,
                            4,
                            5,
                            6,
                            7,
                            8,
                            9,
                            10,
                            11,
                            12,
                            13,
                            14,
                            15,
                            16,
                            17,
                            18,
                            19,
                            20,
                            21,
                            22,
                            23,
                            24,
                            25,
                            26,
                            27,
                            28,
                            29,
                            30
                        ).map((index) => {
                            return (
                                <div
                                    className="p-3 border-t hover:bg-gray-100 flex items-start gap-2"
                                    key={index}
                                >
                                    <svg
                                        className="w-6 h-6"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path d="M17 21.25H7C3.35 21.25 1.25 19.15 1.25 15.5V8.5C1.25 4.85 3.35 2.75 7 2.75H14C14.41 2.75 14.75 3.09 14.75 3.5C14.75 3.91 14.41 4.25 14 4.25H7C4.14 4.25 2.75 5.64 2.75 8.5V15.5C2.75 18.36 4.14 19.75 7 19.75H17C19.86 19.75 21.25 18.36 21.25 15.5V10.5C21.25 10.09 21.59 9.75 22 9.75C22.41 9.75 22.75 10.09 22.75 10.5V15.5C22.75 19.15 20.65 21.25 17 21.25Z" />
                                        <path d="M11.9998 12.87C11.1598 12.87 10.3098 12.61 9.65978 12.08L6.52978 9.58002C6.20978 9.32002 6.14978 8.85002 6.40978 8.53002C6.66978 8.21002 7.13977 8.15003 7.45977 8.41003L10.5898 10.91C11.3498 11.52 12.6398 11.52 13.3998 10.91L14.5798 9.97002C14.8998 9.71002 15.3798 9.76002 15.6298 10.09C15.8898 10.41 15.8398 10.89 15.5098 11.14L14.3298 12.08C13.6898 12.61 12.8398 12.87 11.9998 12.87Z" />
                                        <path d="M19.5 8.75C17.71 8.75 16.25 7.29 16.25 5.5C16.25 3.71 17.71 2.25 19.5 2.25C21.29 2.25 22.75 3.71 22.75 5.5C22.75 7.29 21.29 8.75 19.5 8.75ZM19.5 3.75C18.54 3.75 17.75 4.54 17.75 5.5C17.75 6.46 18.54 7.25 19.5 7.25C20.46 7.25 21.25 6.46 21.25 5.5C21.25 4.54 20.46 3.75 19.5 3.75Z" />
                                    </svg>
                                    <div className="flex flex-col">
                                        <span className="text-gray-900">
                                            Petty cash request has been modified
                                        </span>
                                        <span className="text-gray-400 text-xs font-light">
                                            {moment().format("LLLL")}
                                        </span>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
                <div className="bg-white border-y sticky w-full bottom-0 p-2 text-gray-700 font-medium">
                    <Link href="/">View all Notification</Link>
                </div>
            </motion.div>
        </div>
    );
};

export default Notifications;
