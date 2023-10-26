import { useEffect } from "react";
import { Routes, Route, useNavigate, useLocation } from "react-router-dom";
import PrivateRoutes from "./utils/PrivateRoute";
import { QueryClientProvider, QueryClient } from "react-query";
import { useSelector } from "react-redux";
import { Mainmenu, Apptoster, Loader } from "./components";
import {
  Login,
  Forgot,
  Dashboard,
  AddArea,
  ManageTemplate,
  ManageArea,
} from "./screens";

export const screens = [
  { screen: <Dashboard />, navlink: `dashboard` },
  { screen: <ManageArea />, navlink: `managearea` },
  { screen: <AddArea />, navlink: `addarea` },
  { screen: <ManageTemplate />, navlink: `managetemplate` },
  { screen: <ManageTemplate />, navlink: `agency` },
  { screen: <ManageTemplate />, navlink: `agents` },
  { screen: <ManageTemplate />, navlink: `newspapers` },
  { screen: <ManageTemplate />, navlink: `purchase` },
  { screen: <ManageTemplate />, navlink: `sales` },
  { screen: <ManageTemplate />, navlink: `billing` },
  { screen: <ManageTemplate />, navlink: `reports` },
  { screen: <ManageTemplate />, navlink: `profile` },
  { screen: <ManageTemplate />, navlink: `changepassword` },
  { screen: <ManageTemplate />, navlink: `settings` },
];

const queryClient = new QueryClient();

function App() {
  const navigate = useNavigate();
  const location = useLocation();

  const { token } = useSelector((state) => state.auth);
  useEffect(() => {
    if (token?.accesstoken && location.pathname === `/`) {
      navigate(`/dashboard`, { replace: true });
    }
  }, [token]);
  return (
    <QueryClientProvider client={queryClient}>
      <Routes>
        <Route path="/" element={<Login />} />
        <Route path="/forgot" element={<Forgot />} />
        <Route path="/addarea" element={<AddArea />} />
        <Route element={<PrivateRoutes />}>
          {/* <Route path="/dashboard" element={<Dashboard />} />
          <Route path="/addarea" element={<AddArea />} />
          <Route path="/managetemplate" element={<ManageTemplate />} />
          <Route path="/managearea" element={<ManageArea />} /> */}
          {screens.map((secureitem, index) => (
            <Route
              path={`/${secureitem.navlink}`}
              element={secureitem.screen}
              key={index}
            />
          ))}
        </Route>
      </Routes>
      <Mainmenu />
      <Apptoster />
      <Loader />
    </QueryClientProvider>
  );
}

export default App;
