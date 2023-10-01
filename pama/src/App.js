/*import { Switch, Route } from "react-router-dom";*/
import { Routes ,Route } from 'react-router-dom';
import Login from "./screens/Login";
import Forgot from "./screens/Forgot";

function App() {
  return (
    <Routes>
      <Route path='/' element={<Login/>} />
      <Route path='/forgot' element={<Forgot/>} />
    </Routes>
  );
}

export default App;
