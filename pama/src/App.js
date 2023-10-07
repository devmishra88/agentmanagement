/*import { Switch, Route } from "react-router-dom";*/
import { Routes ,Route } from 'react-router-dom';
import Login from "./screens/Login";
import Forgot from "./screens/Forgot";
import AddArea from './screens/AddArea';
// import ManageArea from './screens/ManageArea';

function App() {
  return (
    <Routes>
      <Route path='/' element={<Login/>} />
      <Route path='/forgot' element={<Forgot/>} />
      <Route path='/addarea' element={<AddArea/>} />
      {/* <Route path='/managearea' element={<ManageArea/>} /> */}
    </Routes>
   
  );
}

export default App;
