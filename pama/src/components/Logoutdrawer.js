import React from "react";
import { useSelector, useDispatch } from "react-redux";
import { confirmLogout, logout } from "../slices/authSlice";

import {
  Grid,
  Typography,
  Box,
  Drawer,
  List,
  Divider,
  ListItem,
  Button,
} from "@mui/material";

function Logoutdrawer() {
  const dispatch = useDispatch();
  const { logoutpopupposition, logoutpopupstatus } = useSelector(
    (state) => state.auth
  );

  const LogoutContent = () => (
    <Box sx={{ width: `100%` }} role="presentation">
      <Grid item xs={12}>
        <Typography
          component={`p`}
          sx={{
            pt: 2,
            pl: 2,
            pr: 2,
            color: `#0000008a`,
          }}
        >
          Are you sure you want to log out?
        </Typography>
        <List>
          <ListItem
            sx={{
              p: 0,
              m: 0,
            }}
          >
            <Button
              sx={{
                p: 0,
                m: 0,
                mb: 1,
                color: `#2196f3`,
                fontSize: 15,
              }}
              size="small"
              onClick={() => {
                dispatch(
                  logout({
                    logoutpopupposition: `bottom`,
                    logoutpopupstatus: false,
                  })
                );
              }}
            >
              Yes
            </Button>
          </ListItem>
          <Divider />
          <ListItem
            sx={{
              p: 0,
              m: 0,
            }}
          >
            <Button
              sx={{
                p: 0,
                m: 0,
                mb: 1,
                color: `#ff3b30`,
                fontSize: 15,
              }}
              color="secondary"
              size="small"
              onClick={() => {
                dispatch(
                  confirmLogout({
                    logoutpopupposition: `bottom`,
                    logoutpopupstatus: false,
                  })
                );
              }}
            >
              No
            </Button>
          </ListItem>
        </List>
      </Grid>
    </Box>
  );

  return (
    <>
      <Drawer
        anchor={logoutpopupposition}
        open={logoutpopupstatus}
        onClose={() =>
          dispatch(
            confirmLogout({
              logoutpopupposition: `bottom`,
              logoutpopupstatus: false,
            })
          )
        }
      >
        {LogoutContent()}
      </Drawer>
    </>
  );
}

export default Logoutdrawer;
