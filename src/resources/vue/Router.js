
import RecurringRoutes from './RecurringRouter'

const Index = () => import('./components/l-limitless-bs4/Index');
const Form = () => import('./components/l-limitless-bs4/Form');
const Show = () => import('./components/l-limitless-bs4/Show');
const SideBarLeft = () => import('./components/l-limitless-bs4/SideBarLeft');
const SideBarRight = () => import('./components/l-limitless-bs4/SideBarRight');

let routes = [

    {
        path: '/bills',
        components: {
            default: Index,
            //'sidebar-left': ComponentSidebarLeft,
            //'sidebar-right': ComponentSidebarRight
        },
        meta: {
            title: 'Accounting :: Sales :: Bills',
            metaTags: [
                {
                    name: 'description',
                    content: 'Bills'
                },
                {
                    property: 'og:description',
                    content: 'Bills'
                }
            ]
        }
    },
    {
        path: '/bills/create',
        components: {
            default: Form,
            //'sidebar-left': ComponentSidebarLeft,
            //'sidebar-right': ComponentSidebarRight
        },
        meta: {
            title: 'Accounting :: Sales :: Bill :: Create',
            metaTags: [
                {
                    name: 'description',
                    content: 'Create Bill'
                },
                {
                    property: 'og:description',
                    content: 'Create Bill'
                }
            ]
        }
    },
    {
        path: '/bills/:id',
        components: {
            default: Show,
            'sidebar-left': SideBarLeft,
            'sidebar-right': SideBarRight
        },
        meta: {
            title: 'Accounting :: Sales :: Bill',
            metaTags: [
                {
                    name: 'description',
                    content: 'Bill'
                },
                {
                    property: 'og:description',
                    content: 'Bill'
                }
            ]
        }
    },
    {
        path: '/bills/:id/copy',
        components: {
            default: Form,
        },
        meta: {
            title: 'Accounting :: Sales :: Bill :: Copy',
            metaTags: [
                {
                    name: 'description',
                    content: 'Copy Bill'
                },
                {
                    property: 'og:description',
                    content: 'Copy Bill'
                }
            ]
        }
    },
    {
        path: '/bills/:id/edit',
        components: {
            default: Form,
        },
        meta: {
            title: 'Accounting :: Sales :: Bill :: Edit',
            metaTags: [
                {
                    name: 'description',
                    content: 'Edit Bill'
                },
                {
                    property: 'og:description',
                    content: 'Edit Bill'
                }
            ]
        }
    }

]

routes = routes.concat(
    routes,
    RecurringRoutes
);

export default routes
