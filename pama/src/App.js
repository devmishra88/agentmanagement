import { Routes ,Route } from 'react-router-dom';
import { QueryClientProvider, QueryClient } from "react-query";
import Login from "./screens/Login";
import Forgot from "./screens/Forgot";
import AddArea from './screens/AddArea';

const queryClient = new QueryClient();

function App() {
  return (
    <QueryClientProvider client={queryClient}>
    <Routes>
      <Route path='/' element={<Login/>} />
      <Route path='/forgot' element={<Forgot/>} />
      <Route path='/addarea' element={<AddArea/>} /> 
    </Routes>
    </QueryClientProvider>
  );
}

export default App;
