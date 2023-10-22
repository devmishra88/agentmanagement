import { Routes, Route } from "react-router-dom";
import PrivateRoutes from "./utils/PrivateRoute";
import { QueryClientProvider, QueryClient } from "react-query";
import { Mainmenu, Apptoster, Loader } from "./components";
import { Login, Forgot, Dashboard, AddArea } from "./screens";

const queryClient = new QueryClient();

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <Routes>
        <Route path="/" element={<Login />} />
        <Route path="/forgot" element={<Forgot />} />
        <Route path="/addarea" element={<AddArea />} />
        <Route element={<PrivateRoutes />}>
          <Route path="/dashboard" element={<Dashboard />} />
          <Route path="/addarea" element={<AddArea />} />
        </Route>
      </Routes>
      <Mainmenu />
      <Apptoster />
      <Loader />
    </QueryClientProvider>
  );
}

export default App;
