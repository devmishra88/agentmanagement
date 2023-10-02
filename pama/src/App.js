/*import { Switch, Route } from "react-router-dom";*/
import { Routes ,Route } from 'react-router-dom';
import Login from "./screens/Login";
import Forgot from "./screens/Forgot";
import AddArea from './screens/AddArea';

function App() {
  return (
    // <Routes>
    //   <Route path='/' element={<Login/>} />
    //   <Route path='/forgot' element={<Forgot/>} />
    // </Routes>
    <AddArea />
  );
}

export default App;
