import { Routes ,Route } from 'react-router-dom';
import { QueryClientProvider, QueryClient } from "react-query";
import Login from "./screens/Login";
import Forgot from "./screens/Forgot";
import AddArea from './screens/AddArea';
import Dashboard from './screens/Dashboard';
// import ManageArea from './screens/ManageArea';
import AddAgent from './screens/AddAgent';

const queryClient = new QueryClient();

function App() {
  return (
    <QueryClientProvider client={queryClient}>
    <Routes>
      <Route path='/' element={<Login/>} />
      <Route path='/forgot' element={<Forgot/>} />
      <Route path='/addarea' element={<AddArea/>} />
      <Route path='/dashboard' element={<Dashboard/>} />
      {/* <Route path='/managearea' element={<ManageArea/>} /> */}
      <Route path='/addagent' element={<AddAgent/>} />
      
    </Routes>
    </QueryClientProvider>
  );
}

export default App;
