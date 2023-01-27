import { useState } from "react";
import Sidebar from "@/Components/Sidebar";
import Navbar from "@/Components/Navbar";
import { ConfigProvider } from "antd";
import { isMobile } from "react-device-detect";

export default function Authenticated({ auth, header, children }) {
    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);
    const [showingSidebar, setShowingSidebar] = useState(false);
    return (
        <ConfigProvider
            theme={{
                token: {
                    colorPrimary: "#00b96b",
                },
            }}
        >
            <div className="min-h-screen bg-gray-100">
                <div>
                    {isMobile ? (
                        ""
                    ) : (
                        <Sidebar setShowingSidebar={setShowingSidebar} auth={auth}/>
                    )}
                    <Navbar auth={auth} showingSidebar={showingSidebar} setShowingSidebar={setShowingSidebar}/>
                </div>
                <main className="lg:ml-80">
                    <div className="py-4">
                        <div className="px-4">{children}</div>
                    </div>
                </main>
            </div>
        </ConfigProvider>
    );
}
