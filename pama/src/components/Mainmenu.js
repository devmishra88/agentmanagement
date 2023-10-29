import React, { Fragment } from "react";
import {
  DashboardIcon,
  EditIcon,
  PersonIcon,
  ExitToAppIcon,
  moduleitems,
  configuration,
} from "../constants";
import { useSelector, useDispatch } from "react-redux";
import { toggleMenu } from "../slices/CommonSlice";
import { confirmLogout, logout } from "../slices/authSlice";
import useSwitchRoute from "../hooks/useSwitchRoute";

import {
  Grid,
  Paper,
  Typography,
  IconButton,
  Box,
  Drawer,
  List,
  Divider,
  ListItem,
  ListItemIcon,
  ListItemText,
} from "@mui/material";

function Mainmenu() {
  const dispatch = useDispatch();
  const { menuposition, menustatus } = useSelector((state) => state.common);
  const switchRoute = useSwitchRoute();

  const list = () => (
    <Box sx={{ width: 250 }} role="presentation">
      <Grid container>
        <Grid item xs={12}>
          <Paper
            sx={{
              textAlign: "center",
              backgroundColor: "rgb(13, 35, 72)",
              color: "rgb(255, 255, 255)",
              padding: "25px 0px",
              position: "relative",
              borderRadius: 0,
            }}
          >
            <IconButton
              sx={{ position: "absolute", top: 5, right: 5, color: `#ffffff` }}
              onClick={() => {
                switchRoute(`/profile`, false);
              }}
            >
              <EditIcon />
            </IconButton>
            <PersonIcon fontSize="large" />
            <Typography variant="h6">Devesh Mishra</Typography>
            <Typography variant="subtitle1">9999892383</Typography>
          </Paper>
        </Grid>
        <Grid item xs={12}>
          <List>
            <ListItem
              onClick={() => {
                switchRoute(`/dashboard`, false);
              }}
            >
              <ListItemIcon
                sx={{
                  color: `#488a9a`,
                }}
              >
                <DashboardIcon />
              </ListItemIcon>
              <ListItemText primary="Dashboard" sx={{ color: `#007aff` }} />
            </ListItem>
            <Divider />
            {moduleitems.map((item, index) => (
              <Fragment key={index}>
                <ListItem
                  onClick={() => {
                    switchRoute(`${item.link}`, false);
                  }}
                >
                  <ListItemIcon
                    sx={{
                      color: item.iconcolor,
                    }}
                  >
                    {item.iconname}
                  </ListItemIcon>
                  <ListItemText
                    primary={item.title}
                    sx={{
                      color: item.titlecolor,
                    }}
                  />
                </ListItem>
                <Divider />
              </Fragment>
            ))}
            {configuration.map((configitem, configindex) => (
              <Fragment key={configindex}>
                <ListItem
                  onClick={() => {
                    switchRoute(`${configitem.link}`, false);
                  }}
                >
                  <ListItemIcon
                    sx={{
                      color: configitem.iconcolor,
                    }}
                  >
                    {configitem.iconname}
                  </ListItemIcon>
                  <ListItemText
                    primary={configitem.title}
                    sx={{
                      color: configitem.titlecolor,
                    }}
                  />
                </ListItem>
                <Divider />
              </Fragment>
            ))}
            <ListItem
              onClick={() => {
                dispatch(toggleMenu({ menuposition: `left`, menustatus: false }))
                dispatch(
                  confirmLogout({
                    logoutpopupposition: `bottom`,
                    logoutpopupstatus: true,
                  })
                );
              }}
            >
              <ListItemIcon
                sx={{
                  color: `#d32d41`,
                }}
              >
                <ExitToAppIcon />
              </ListItemIcon>
              <ListItemText primary="Logout" sx={{ color: `#007aff` }} />
            </ListItem>
          </List>
        </Grid>
      </Grid>
    </Box>
  );

  return (
    <>
      <Drawer
        anchor={menuposition}
        open={menustatus}
        onClose={() =>
          dispatch(toggleMenu({ menuposition: `left`, menustatus: false }))
        }
      >
        {list()}
      </Drawer>
    </>
  );
}

export default Mainmenu;
