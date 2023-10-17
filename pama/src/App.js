import { Routes ,Route } from 'react-router-dom';
import { QueryClientProvider, QueryClient } from "react-query";
import Login from "./screens/Login";
import Forgot from "./screens/Forgot";
import AddArea from './screens/AddArea';
import Dashboard from './screens/Dashboard';

const queryClient = new QueryClient();

function App() {
  return (
    <QueryClientProvider client={queryClient}>
    <Routes>
      <Route path='/' element={<Login/>} />
      <Route path='/forgot' element={<Forgot/>} />
      <Route path='/addarea' element={<AddArea/>} />
      <Route path='/dashboard' element={<Dashboard/>} />
      
    </Routes>
    </QueryClientProvider>
  );
}

export default App;
