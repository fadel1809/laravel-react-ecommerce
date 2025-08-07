import { Link } from "@inertiajs/react";

export default function Landingpage (){
    return (
        <div>
            <h1 className="text-red text-lg">Landingpage</h1>
            <button className="btn btn-primary">Fadel</button>
            <button className="btn btn-secondary">
            <Link href={route('login')}> go to login</Link>
            </button>
        </div>
    );
}
