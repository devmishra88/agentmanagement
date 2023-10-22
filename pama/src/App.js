import { useEffect } from "react";
import { Routes, Route, useNavigate } from "react-router-dom";
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

const queryClient = new QueryClient();

function App() {
  const navigate = useNavigate();
  const { token } = useSelector((state) => state.auth);
  useEffect(() => {
    if (token?.accesstoken) {
      navigate(`/dashboard`);
    }
  }, [token]);
  return (
    <QueryClientProvider client={queryClient}>
      <Routes>
        <Route path="/" element={<Login />} />
        <Route path="/forgot" element={<Forgot />} />
        <Route path="/addarea" element={<AddArea />} />
        <Route element={<PrivateRoutes />}>
          <Route path="/dashboard" element={<Dashboard />} />
          <Route path="/addarea" element={<AddArea />} />
          <Route path="/managetemplate" element={<ManageTemplate />} />
          <Route path="/managearea" element={<ManageArea />} />
        </Route>
      </Routes>
      <Mainmenu />
      <Apptoster />
      <Loader />
    </QueryClientProvider>
  );
}

export default App;
