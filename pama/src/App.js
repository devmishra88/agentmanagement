import { useEffect, lazy, Suspense } from "react";
import { Routes, Route, useNavigate, useLocation } from "react-router-dom";
import PrivateRoutes from "./utils/PrivateRoute";
import { QueryClientProvider, QueryClient } from "react-query";
import { useSelector, useDispatch } from "react-redux";
import { toggleLoader } from "./slices/CommonSlice";
import {
  Mainmenu,
  Apptoster,
  Loader,
  Logoutdrawer,
  Deleteconfirm,
} from "./components";

import { moduleitems, configuration } from "./constants";

import { /*Dashboard,*/ publicscreens, secureaddscreens } from "./screens";
const Dashboard = lazy(() => import("./screens/Dashboard"));

const queryClient = new QueryClient();

function App() {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const location = useLocation();

  const { token } = useSelector((state) => state.auth);
  useEffect(() => {
    if (token?.accesstoken && location.pathname === `/`) {
      dispatch(toggleLoader({ loaderstatus: false }));
      navigate(`/dashboard`, { replace: true });
    }
  }, [token]);
  return (
    <QueryClientProvider client={queryClient}>
      <Routes>
        {publicscreens.map((publicitem, publicindex) => (
          <Route
            path={publicitem.navlink}
            element={publicitem.screen}
            key={publicindex}
          />
        ))}

        <Route element={<PrivateRoutes />}>
          {/* <Suspense fallback={<div>Loading...</div>}>
            <Route path="/dashboard" element={<Dashboard />} />
          </Suspense> */}
          <Route path="/dashboard" element={<Dashboard />} />
          {secureaddscreens.map((secureitem, secureaddindex) => (
            <Route
              path={secureitem.navlink}
              element={secureitem.screen}
              key={secureaddindex}
            />
          ))}
          {moduleitems.map((moduleitem, moduleitemindex) => (
            <Route
              path={moduleitem.link}
              element={moduleitem.screen}
              key={moduleitemindex}
            />
          ))}
          {configuration.map((configitem, configitemindex) => (
            <Route
              path={configitem.link}
              element={configitem.screen}
              key={configitemindex}
            />
          ))}
        </Route>
      </Routes>
      <Mainmenu />
      <Apptoster />
      <Loader />
      <Logoutdrawer />
      <Deleteconfirm />
    </QueryClientProvider>
  );
}

export default App;
