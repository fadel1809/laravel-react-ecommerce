import ApplicationLogo from "@/Components/App/ApplicationLogo";
import Navbar from "@/Components/App/Navbar";
import { Link, usePage } from "@inertiajs/react";
import { PropsWithChildren, ReactNode, useEffect, useRef, useState } from "react";

export default function AuthenticatedLayout({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const user = usePage().props.auth.user;
    const props = usePage().props
    const [successMessage, setSuccessMessage] = useState<any[]>([]);
    const timeOutRefs = useRef<{
        [key: number]: ReturnType<typeof setTimeout>;
    }>({});
    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);
    useEffect(()=>{
        if(props.success.message){
            const newMessage = {
                ...props.success,
                id: props.success.time
            }
        
        setSuccessMessage((prevMessage) => [newMessage,...prevMessage])

        const timeoutId = setTimeout(() => {
            setSuccessMessage((prevMessage) => prevMessage.filter((msg) => msg.id !== newMessage.id))
            delete timeOutRefs.current[newMessage.id];
        },5000)
        timeOutRefs.current[newMessage.id] = timeoutId    
    }
    },[props.success])
    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
            <Navbar />
            {header && (
                <header className="bg-white shadow dark:bg-gray-800">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}
            {
                successMessage.length > 0 && (
                    <div className="toast toast-top toast-end z-[1000] mt-16">
                        {
                            successMessage.map((msg) =>(
                                <div className="alert alert-success" key={msg.id}>
                                    <span>{msg.message}</span>
                                </div>
                            ))
                        }
                    </div>
                )
            }

            <main>{children}</main>
        </div>
    );
}
